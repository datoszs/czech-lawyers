<?php
namespace App\Commands;


use App\Enums\Court;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Utils\Helpers;
use App\Utils\Normalize;
use app\Utils\JobCommand;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OfficialDataImport extends Command
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

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_FILE = 1;
	const RETURN_CODE_INVALID_NAMES = 2;
	const RETURN_CODE_INVALID_SKIP = 3;
	const RETURN_CODE_INVALID_COURT = 4;

	/** @var CauseService @inject */
	public $causeService;

	/** @var CourtService @inject */
	public $courtService;

	protected function configure()
	{
		$this->setName('app:import-official-data')
			->setDescription('Imports information from official data (aggregates, make them unique and overwrite old data). May require more of memory_limit.')
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
				'Names of all columns delimited by |. Must match count of columns in the CSV file.'
			)
			->addOption(
				static::OPTION_SKIP,
				static::OPTION_SKIP_SHORTCUT,
				InputOption::VALUE_OPTIONAL,
				'Indexes of columns (from 1) which will be skipped. Delimited by |.'
			);
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
	 * @return array where first is number of newly inserted and second number of overwritten items.
	 */
	public function processFile(OutputInterface $consoleOutput, $courtId, $file, $delimiter, $keys, $skip)
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
		$overwritten = 0;
		foreach ($toPersist as $registryMark => $caseData) {
			$year = Helpers::determineYear($registryMark);
			$entity = $this->causeService->findOrCreate($court, $year, $registryMark); // explicitly create case when not already exists
			if ($entity->officialData) {
				$overwritten++;
			} else {
				$new++;
			}
			$entity->officialData = array_values(array_unique($caseData, SORT_REGULAR));
			$this->causeService->save($entity);
		}
		return [$new, $overwritten];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$file = $input->getArgument(static::ARGUMENT_FILE);
		$delimiter = $input->getOption(static::OPTION_DELIMITER);
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
			list($new, $overwritten) = $this->processFile($consoleOutput, Court::$types[$court], $file, $delimiter, $keys, $skip);
			if ($new > 0 || $overwritten > 0) {
				$this->causeService->flush();
			}
			$message = "Inserted new information to {$new} cases and {$overwritten} overwritten with new data.\n";
			$consoleOutput->write($message);
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
	}
}
