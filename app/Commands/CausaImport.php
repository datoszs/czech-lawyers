<?php
namespace App\Commands;


use App\Enums\Court;
use App\Model\Documents\Document;
use App\Model\Documents\DocumentLawCourt;
use App\Model\Documents\DocumentSupremeAdministrativeCourt;
use App\Model\Documents\DocumentSupremeCourt;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Model\Services\DocumentService;
use App\Utils\Normalize;
use App\Utils\Validators;
use app\Utils\JobCommand;
use DateTime;
use League\Csv\Reader;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CausaImport extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_COURT = 'court';
	const ARGUMENT_DIRECTORY = 'directory';
	const OVERWRITE_FILES = 'overwrite';
	const OVERWRITE_FILES_SHORTCUT = 'o';

	const DOCUMENTS_DIRECTORY_PROJECT_RELATIVE = 'storage/documents';
	const DOCUMENTS_DIRECTORY = __DIR__ . '/../../storage/documents';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_DIR = 1;
	const INVALID_CONTENT = 2;
	const RETURN_CODE_INVALID_COURT = 3;

	/** @var CourtService @inject */
	public $courtService;
	/** @var DocumentService @inject */
	public $documentService;
	/** @var CauseService @inject */
	public $causeService;

	protected function configure()
	{
		$this->setName('app:import-documents')
			->setDescription('Imports data from one crawler at the time.')
			->addOption(
				static::OVERWRITE_FILES,
				static::OVERWRITE_FILES_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should the files be overwritten? Default is false.'
			)->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				sprintf('Court which is imported, available values are: %s', implode(', ', array_keys(Court::$types)))
			)->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where final crawler data are ready for import. (The result directory)'
			);
	}

	/**
	 * Expects validate directory with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param int $courtId ID of court
	 * @param string $directory
	 * @param boolean $overwrite whether the files should be overwritten
	 * @return array where first is number of imported and second number of duplicated items.
	 */
	public function processDirectory(OutputInterface $consoleOutput, $courtId, $directory, $overwrite)
	{
		$csv = Reader::createFromPath($directory . '/metadata.csv');
		$csv->setDelimiter(';');
		$court = $this->courtService->getById($courtId);
		$destinationDir = static::getCourtDirectory($courtId);
		$destinationDirRelative = static::getCourtDirectory($courtId, true);
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAssoc($this->getColumnNames($courtId));
		$imported = 0;
		$duplicated = 0;
		foreach ($rows as $row) {
			// Check if not duplicate
			$recordId = Normalize::recordId($row['record_id']);
			$entity = $this->documentService->findByRecordId($recordId);

			if ($entity) {
				$duplicated++;
				$consoleOutput->writeln(sprintf("Warning: record with ID %s already found in database.", $row['record_id']));
				continue;
			}
			// Prepare for insert
			$document = new Document();
			$document->recordId = $recordId;
			$document->court = $court;
			$document->webPath = (string) $row['web_path'];
			$document->localPath = $destinationDirRelative . (string) $row['local_path'];
			$document->decisionDate = new DateTime($row['decision_date']);
			$document->case = $this->causeService->findOrCreate($court, Normalize::registryMark($row['registry_mark']), $this->jobRun);
			$document->jobRun = $this->jobRun;
			$extras = null;
			if ($courtId == Court::TYPE_NS) {
				$extras = new DocumentSupremeCourt();
				$extras->document = $document;
				$extras->ecli = $row['ecli'];
				$extras->decisionType = $row['decision_type'];
			} elseif ($courtId == Court::TYPE_NSS) {
				$extras = new DocumentSupremeAdministrativeCourt();
				$extras->document = $document;
				$extras->orderNumber = $row['order_number'];
				$extras->decision = $row['decision'];
				$extras->decision_type = $row['decision_type'];
			} elseif ($courtId == Court::TYPE_US) {
				$extras = new DocumentLawCourt();
				$extras->document = $document;
				$extras->ecli = $row['ecli'];
				$extras->form_decision = $row['form_decision'];
				$extras->decision_result = $row['decision_result'];
			}
			// Store to database
			$this->documentService->insert($document, $extras);
			$imported++;
			// Copy document file into destination folder
			$destinationPath = $destinationDir . $row['local_path'];
			if (!$overwrite && file_exists($destinationPath)) {
				$consoleOutput->writeln(sprintf("Warning: Skipping file %s as it already exists in %s.", $row['local_path'], $destinationDirRelative));
				continue;
			}
			copy($directory . '/documents/' . $row['local_path'], $destinationPath); // copy immediately - better to have not referenced files than documents in database without their files.
		}
		return [$imported, $duplicated];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$directory = $input->getArgument(static::ARGUMENT_DIRECTORY);
		$overwrite = $input->getOption(static::OVERWRITE_FILES);
		$code = 0;
		$output = null;
		$message = null;
		if (!isset(Court::$types[$court])) {
			$message = 'Error: The given court is not valid value.';
			$code = static::RETURN_CODE_INVALID_COURT;
			$consoleOutput->writeln($message);
		} elseif (!FileSystem::isAbsolute($directory)) {
			$message = 'Error: The given path has to be absolute.';
			$code = static::RETURN_CODE_INVALID_DIR;
			$consoleOutput->writeln($message);
		} elseif (!is_dir($directory) || !is_readable($directory)) {
			$message = 'Error: The given path is not directory or not readable.';
			$code = static::RETURN_CODE_INVALID_DIR;
			$consoleOutput->writeln($message);
		} elseif (!$this->validateInputDirectory($directory)) {
			$message = 'Error: The given path doesn\'t contain metadata.csv or documents... or contains another files/directories.';
			$code = static::INVALID_CONTENT;
			$consoleOutput->writeln($message);
		} elseif (!is_bool($overwrite)) {
			$message = 'Error: The overwrite flag has to be .';
			$code = static::INVALID_CONTENT;
			$consoleOutput->writeln($message);
		} else {
			// import to db
			list($imported, $duplicated) = $this->processDirectory($consoleOutput, Court::$types[$court], $directory, $overwrite);
			if ($imported > 0) {
				$this->documentService->flush();
			}
			$message = "Imported {$imported} documents ({$duplicated} duplicate).\n";
			$consoleOutput->write($message);
			// Empty directory after successful procession
			FileSystem::delete($directory . '/metadata.csv');
			FileSystem::delete($directory . '/documents/');
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
	}

	private function getCourtDirectory($court, $projectRelative = false)
	{
		$baseDir = static::DOCUMENTS_DIRECTORY;
		if ($projectRelative) {
			$baseDir = static::DOCUMENTS_DIRECTORY_PROJECT_RELATIVE;
		}
		switch($court) {
			case Court::TYPE_NS:
				return $baseDir . '/ns/';
			case Court::TYPE_NSS:
				return $baseDir . '/nss/';
			case Court::TYPE_US:
				return $baseDir . '/us/';
		}
	}

	private function getColumnNames($court)
	{
		switch($court) {
			case Court::TYPE_NS:
				return ['court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'ecli', 'decision_type'];
			case Court::TYPE_NSS:
				return ['court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'decision_type', 'decision', 'order_number'];
			case Court::TYPE_US:
				return ['court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'form_decision', 'decision_result', 'ecli'];
		}
	}
}
