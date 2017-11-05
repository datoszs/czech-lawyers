<?php
namespace App\Commands;


use App\Enums\Court;
use App\Model\Annulments\Annulment;
use App\Model\Cause\Cause;
use App\Model\Services\AnnulmentService;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Utils\Helpers;
use App\Utils\Normalize;
use App\Utils\JobCommand;
use DateTimeImmutable;
use League\Csv\Reader;
use Nette\Neon\Exception;
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
	const ARGUMENT_COURT = 'court';
	const OPTION_DELIMITER = 'delimiter';
	const OPTION_DELIMITER_SHORTCUT = 'd';
	const OPTION_KEYS = 'keys';
	const OPTION_KEYS_SHORTCUT = 'k';
	const OPTION_SKIP = 'skip';
	const OPTION_SKIP_SHORTCUT = 's';
	const OPTION_DRY_RUN = 'dry-run';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_FILE = 1;
	const RETURN_CODE_INVALID_NAMES = 2;
	const RETURN_CODE_INVALID_SKIP = 3;
	const RETURN_CODE_INVALID_COURT = 4;
	const RETURN_CODE_INVALID_DATE_FORMAT = 5;

	/** @var CauseService @inject */
	public $causeService;

	/** @var CourtService @inject */
	public $courtService;

	/** @var AnnulmentService @inject */
	public $annulmentService;

	protected function configure()
	{
		$this->setName('app:import-annuled-case')
			->setDescription('Imports information about annulments. Expects registry mark in first column.')
			->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				sprintf('Court which is imported, available values are: %s', implode(', ', array_keys(Court::$types)))
			)->addArgument(
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
				static::OPTION_KEYS,
				static::OPTION_KEYS_SHORTCUT,
				InputOption::VALUE_REQUIRED,
				'Stored names of all columns delimited by |. Must match count of columns in the CSV file. Names should be the same as already used in given court previously stored official data.'
			)->addOption(
				static::OPTION_SKIP,
				static::OPTION_SKIP_SHORTCUT,
				InputOption::VALUE_OPTIONAL,
				'Indexes of columns (from 1) which will be skipped. Delimited by |.'
			)->addOption(
				static::OPTION_DRY_RUN,
				null,
				InputOption::VALUE_NONE,
				'Dry run, nothing is persisted, just printed to standard output.'
			);
	}

	protected function convertRegistryMark(string $registryMark) {
		$temp = explode('/', $registryMark);
		if (count($temp) !== 2) {
			throw new InvalidArgumentException("Invalid registry mark [$registryMark], cannot convert.");
		}
		if (strlen($temp[1]) === 2 && NetteValidators::isNumericInt($temp[1])) {
			return $registryMark;
		}
		if (strlen($temp[1]) === 4 && NetteValidators::isNumericInt($temp[1])) {

			return implode("/",[$temp[0], substr($temp[1], -2, 2)]); //last two digits of year
		}
		throw new InvalidArgumentException("Invalid registry mark [$registryMark], unknown error.");
	}

	/**
	 * Expects file path with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param int $courtId
	 * @param string $file
	 * @param string $delimiter
	 * @param array $keys
	 * @param array $skip
	 * @param bool $dryRun
	 * @return array where first is number of newly inserted and second number of overwritten items.
	 * @throws Exception
	 */
	public function processFile(OutputInterface $consoleOutput, $courtId, $file, $delimiter, $keys, $skip, bool $dryRun)
	{
		// Ensure that the file is in UTF-8
		if (!mb_check_encoding(file_get_contents($file), 'UTF-8')) {
			throw new InvalidArgumentException('Given file is not in UTF-8, before using this tool, please convert input file.');
		}
		// Get court
		$court = $this->courtService->getById($courtId);
		// Expects registry sign in first column, all other columns are objectized and stored
		$csv = Reader::createFromPath($file);
		$csv->setDelimiter($delimiter);
		$firstRow = $csv->fetchOne();
		if (!$firstRow || count($firstRow) - 1 - count($skip) != count($keys)) {
			throw new InvalidArgumentException(sprintf('Invalid number of keys provided, the document has [%s] available. You have provided [%s] keys.', ($firstRow) ? count($firstRow) - 1 - count($skip) : 0, count($keys)));
		}
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAll();

		// Agregate results
		$toPersist = [];
		foreach ($rows as $row) {
			$registryMark = Normalize::registryMark($row[0]);
			// Prepare data item
			$tuple = $row;
			unset($tuple[0]);
			foreach ($skip as $index) {
				unset($tuple[$index]);
			}
			$item = array_combine($keys, $tuple);
			if (!isset($toPersist[$registryMark])) {
				$toPersist[$registryMark] = [];
			}
			$toPersist[$registryMark][] = $item;
		}
		// Store aggregated result
		$new = 0;
		$bad = 0;
		foreach ($toPersist as $registryMark => $caseData) {
			//$year = Helpers::determineYear($registryMark);

			/* @var Cause $badCase */
			/* @var Cause $annuledCase */
			/* @var Cause $annulingCase */

			$annulingCaseRegistryMark = $caseData[0]['annuling_case'];

			if ($court == Court::TYPE_US) {
				$registryMark = $this->convertRegistryMark($registryMark); // make short year from long (4 digits)
			}

			$annuledCase = $this->causeService->find($registryMark); // checking existence of case
			$annulingCase = $this->causeService->find(Normalize::registryMark($this->convertRegistryMark($annulingCaseRegistryMark))); // checking existence of annuling case

			$badCase = null;
			if ($annuledCase == null) {
				$consoleOutput->writeln(sprintf('Case (annuled) with registry mark [%s] not exists.',$registryMark));
				$bad++;
			}
			if ($annulingCase == null) {
				$consoleOutput->writeln(sprintf('Case (annuling) with registry mark [%s] not exists.',$annulingCaseRegistryMark));
				$bad++;
			}
			if ($annulingCase == null || $annuledCase == null) {
				continue;
			}

			$entity = $this->annulmentService->findPair($annuledCase, $annulingCase);

			if ($entity != null) {
				//$consoleOutput->writeln("Existuje: " . $annuledCase->registrySign . "; " . $annulingCase->registrySign);
				continue;
			}

			//$consoleOutput->writeln("Vkladam: ".$annuledCase->registrySign. "; ". $annulingCase->registrySign);

			// Store result
			if ($dryRun) {
				$consoleOutput->writeln(sprintf("%s\n%s\n\n", $registryMark, print_r($caseData, true)));
			} else {
				$new++;
				$this->annulmentService->createAnnulment($annuledCase, $annulingCase, $this->jobRun);
			}
		}
		/* @var Annulment $entity */
		$entity = $this->annulmentService->findByCaseId(131);
		$consoleOutput->writeln($entity->annuled_case);
		return [$new, $bad];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$file = $input->getArgument(static::ARGUMENT_FILE);
		$delimiter = $input->getOption(static::OPTION_DELIMITER);
		$dryRun = (bool) $input->getOption(static::OPTION_DRY_RUN);
		$keys = Helpers::safeExplode('|', $input->getOption(static::OPTION_KEYS));
		$skip = Helpers::safeExplode('|', $input->getOption(static::OPTION_SKIP));
		$code = 0;
		$output = null;
		$message = null;
		if (!isset(Court::$types[$court])) {
			$message = 'Error: The given court is not valid value.';
			$code = static::RETURN_CODE_INVALID_COURT;
			$consoleOutput->writeln($message);
		} elseif (!is_file($file) || !is_readable($file)) {
			$message = 'Error: The given path is not file or not readable.';
			$code = static::RETURN_CODE_INVALID_FILE;
		} elseif (!$keys || count($keys) == 0) {
			$message = 'Error: Key names were not provided.';
			$code = static::RETURN_CODE_INVALID_NAMES;
		} elseif ($skip && !Helpers::isIntArray($skip)) {
			$message = 'Error: List of columns to skip has to be numerical.';
			$code = static::RETURN_CODE_INVALID_SKIP;
		} else {
			// import to db
			list($new, $bad) = $this->processFile($consoleOutput, Court::$types[$court], $file, $delimiter, $keys, $skip, $dryRun);
			if (!$dryRun && ($new > 0 || $bad > 0)) {
				$this->annulmentService->flush();
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
