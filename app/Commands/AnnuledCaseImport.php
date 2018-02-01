<?php declare(strict_types=1);

namespace App\Commands;


use App\Model\Annulments\Annulment;
use App\Model\Cause\Cause;
use App\Model\Services\AnnulmentService;
use App\Model\Services\CauseService;

use App\Utils\Helpers;
use App\Utils\Normalize;
use App\Utils\JobCommand;
use DateTimeImmutable;
use League\Csv\Reader;
use Nette\Neon\Exception;
use Nette\Utils\Strings;
use Nextras\Dbal\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\Utils\Validators as NetteValidators;

class AnnuledCaseImport extends Command
{
	use JobCommand;

	const ARGUMENT_FILE = 'file';
	const OPTION_DELIMITER = 'delimiter';
	const OPTION_DELIMITER_SHORTCUT = 'd';
	const OPTION_DRY_RUN = 'dry-run';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_FILE = 1;

	/** @var CauseService @inject */
	public $causeService;


	/** @var AnnulmentService @inject */
	public $annulmentService;

	/** @var Connection @inject */
	public $connection;

	protected function configure()
	{
		$this->setName('app:import-annuled-case')
			->setDescription('Imports information about annulments. Expects registry mark in first column.')
			->addArgument(
				static::ARGUMENT_FILE,
				InputArgument::REQUIRED,
				'CSV file with official data to be imported.'
			)->addOption(
				static::OPTION_DELIMITER,
				static::OPTION_DELIMITER_SHORTCUT,
				InputOption::VALUE_OPTIONAL,
				'Delimiter character, default is ;.',
				';'
			)->addOption(
				static::OPTION_DRY_RUN,
				null,
				InputOption::VALUE_NONE,
				'Dry run, nothing is persisted, just printed to standard output.'
			);
	}

	protected function convertRegistryMark(string $registryMark)
	{
		$temp = explode('/', $registryMark);
		if (count($temp) !== 2) {
			throw new InvalidArgumentException("Invalid registry mark [$registryMark], cannot convert.");
		}
		if (strlen($temp[1]) === 2 && NetteValidators::isNumericInt($temp[1])) {
			return $registryMark;
		}
		if (strlen($temp[1]) === 4 && NetteValidators::isNumericInt($temp[1])) {
			return implode("/", [$temp[0], substr($temp[1], -2, 2)]); //last two digits of year
		}
		throw new InvalidArgumentException("Invalid registry mark [$registryMark], unknown error.");
	}

	protected function findWithConversion(string $registryMark)
	{
		/* @var Cause $case */
		$case = $this->causeService->find($registryMark);
		if ($case == null && Strings::contains($registryMark, 'ús')) {
			$case = $this->causeService->find($this->convertRegistryMark($registryMark)); // make short year from long (4 digits)
		}
		return $case;
	}

	/**
	 * Expects file path with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param string $file
	 * @param string $delimiter
	 * @param bool $dryRun
	 * @return array where first is number of newly inserted and second number of overwritten items.
	 */
	public function processFile(OutputInterface $consoleOutput, $file, $delimiter, bool $dryRun)
	{
		// Ensure that the file is in UTF-8
		if (!mb_check_encoding(file_get_contents($file), 'UTF-8')) {
			throw new InvalidArgumentException('Given file is not in UTF-8, before using this tool, please convert input file.');
		}

		$csv = Reader::createFromPath($file);
		$csv->setDelimiter($delimiter);
		$firstRow = $csv->fetchOne();
		if (!$firstRow || count($firstRow) < 2) {
			throw new InvalidArgumentException(sprintf('Empty file or bad delimiter.'));
		}
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAll();
		$new = 0;
		$bad = 0;
		foreach ($rows as $row) {
			$item = array_combine($firstRow, $row);
			$annuledRegistryMark = Normalize::registryMark($item[$firstRow[0]]);
			$annulingRegistryMark = Normalize::registryMark($item[$firstRow[1]]);

			/* @var Cause $annuledCase */
			/* @var Cause $annulingCase */
			$annuledCase = $this->findWithConversion($annuledRegistryMark);
			$annulingCase = $this->findWithConversion($annulingRegistryMark);
			if ($annuledCase == null) {
				$consoleOutput->writeln(sprintf('Case (annuled) with registry mark [%s] not exists.', $annuledRegistryMark));
				$bad++;
				continue;
			}
			if ($annulingCase == null && $annulingRegistryMark != null) {
				$consoleOutput->writeln(sprintf('Case (annuling) with registry mark [%s] not exists.', $annulingRegistryMark));
				$bad++;
				continue;
			}

			$entity = $this->annulmentService->getPair($annuledCase, $annulingCase); // try if now exist

			if ($entity != null) {
				//$consoleOutput->writeln("Existuje: " . $annuledCase->registrySign . "; " . $annulingCase->registrySign);
				continue;
			}

			// Store result
			if ($dryRun) {
				$consoleOutput->writeln(sprintf("%s\n%s\n\n", $annuledRegistryMark, print_r($item)));
			} else {
				$new++;
				$this->annulmentService->createAnnulment($annuledCase, $annulingCase, $this->jobRun);
			}
		}
		return [$new, $bad];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$file = $input->getArgument(static::ARGUMENT_FILE);
		$delimiter = $input->getOption(static::OPTION_DELIMITER);
		$dryRun = (bool)$input->getOption(static::OPTION_DRY_RUN);
		$code = 0;
		$output = null;
		$message = null;
		if (!is_file($file) || !is_readable($file)) {
			$message = 'Error: The given path is not file or not readable.';
			$code = static::RETURN_CODE_INVALID_FILE;
		} else {
			// import to db
			list($new, $bad) = $this->processFile($consoleOutput, $file, $delimiter, $dryRun);
			if (!$dryRun && ($new > 0 || $bad > 0)) {
				$this->annulmentService->flush();
			} else {
				$this->connection->rollbackTransaction();
			}
			if ($dryRun) {
				$message = "[Dry run] Inserted new information about {$new} cases. Not exist {$bad} cases.\n";
			} else {
				$message = "Inserted new information about {$new} cases. Not exist {$bad} cases.\n";
			}
			$consoleOutput->write($message);
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
	}
}
