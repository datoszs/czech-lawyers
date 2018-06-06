<?php

namespace App\Commands;


use App\Enums\Court;
use App\Model\Cause\Cause;
use App\Model\Court\Court as CourtEntity;
use App\Model\Documents\Document;
use App\Model\Documents\DocumentConstitutionalCourt;
use App\Model\Documents\DocumentLawCourt;
use App\Model\Documents\DocumentSupremeAdministrativeCourt;
use App\Model\Documents\DocumentSupremeCourt;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Model\Services\DocumentService;
use App\Utils\Helpers;
use App\Utils\Normalize;
use App\Utils\Validators;
use App\Utils\JobCommand;
use DateTime;
use League\Csv\Reader;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
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
	const UPDATE_RECORDS = "update";
	const UPDATE_RECORDS_SHORTCUT = "u";

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

	/** @var CourtEntity */
	private $court;

	protected function configure()
	{
		$this->setName('app:import-documents')
			->setDescription('Imports data from one crawler at the time.')
			->addOption(
				static::OVERWRITE_FILES,
				static::OVERWRITE_FILES_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should the files be overwritten? Default is false.'
			)->addOption(
				static::UPDATE_RECORDS,
				static::UPDATE_RECORDS_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should the records be updated (only NSS, ÚS)? Default is false.'
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

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$directory = $input->getArgument(static::ARGUMENT_DIRECTORY);
		$overwrite = $input->getOption(static::OVERWRITE_FILES);
		$update = $input->getOption(static::UPDATE_RECORDS);
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
			$message = 'Error: The overwrite flag has to be.';
			$code = static::INVALID_CONTENT;
			$consoleOutput->writeln($message);
		} elseif (!is_bool($update)) {
			$message = 'Error: The update flag has to be.';
			$code = static::INVALID_CONTENT;
			$consoleOutput->writeln($message);
		} else {
			// State explicitly to
			$courtEntity = $this->getCourt(Court::$types[$court]);
			$temp = sprintf("Court: %s\n", $courtEntity->name);
			$output .= $temp;
			$consoleOutput->write($temp);
			// import to db
			list($imported, $duplicated) = $this->processDirectory($consoleOutput, $output, Court::$types[$court], $directory, $overwrite, $update);
			if ($imported > 0) {
				$this->documentService->flush();
			}
			$message = "Imported {$imported} documents ({$duplicated} duplicate).\n";
			$output .= $message;
			$consoleOutput->write($message);
			// Empty directory after successful procession
			FileSystem::delete($directory . '/metadata.csv');
			FileSystem::delete($directory . '/documents/');
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}

	/**
	 * Expects validate directory with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param string $output Output to be stored
	 * @param int $courtId ID of court
	 * @param string $directory
	 * @param boolean $overwrite whether the files should be overwritten
	 * @param boolean $update whether the records should be updated
	 * @return array where first is number of imported and second number of duplicated items.
	 * @throws \Nette\Utils\JsonException
	 */
	public function processDirectory(OutputInterface $consoleOutput, &$output, $courtId, $directory, $overwrite, $update)
	{
		$csv = Reader::createFromPath($directory . '/metadata.csv');
		$csv->setDelimiter(';');
		$court = $this->getCourt($courtId);
		$destinationDir = static::getCourtDirectory($courtId);
		$destinationDirRelative = static::getCourtDirectory($courtId, true);
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAssoc($this->getColumnNames($courtId));
		$imported = 0;
		$duplicated = 0;
		foreach ($rows as $row) {
			// Check if not duplicate
			$recordId = Normalize::recordId($row['record_id']);
			$entity = null;
			$toUpdate = null;
			$document = null;
			$extras = null;
			$entity = $this->documentService->findByRecordId($recordId);

			if ($entity && !$update) {
				$duplicated++;
				$output .= sprintf("Warning: record with ID %s already found in database.", $row['record_id']) . "\n";
				continue;
			}

			if (!$update) {
				// Prepare for insert
				$document = new Document();
				$document->recordId = $recordId;
				$document->court = $court;
				$document->webPath = (string)$row['web_path'];
				$document->localPath = $destinationDirRelative . (string)$row['local_path'];
				$document->decisionDate = new DateTime($row['decision_date']);
				// Year in ECLI can be different from date in registry_mark
				$document->case = $this->causeService->findOrCreate($court, Normalize::registryMark($row['registry_mark']), $this->getYear($row), $this->jobRun);
				$document->jobRun = $this->jobRun;
			} elseif ($entity && $update) {
				// if update and exist document find extra data
				$document = $entity;
				$document->webPath = (string)$row['web_path'];
				$document->localPath = $destinationDirRelative . (string)$row['local_path'];
				$document->decisionDate = new DateTime($row['decision_date']);
				$toUpdate = $this->documentService->findExtraData($document);
			}
			if ($courtId == Court::TYPE_NS) {
				$extras = new DocumentSupremeCourt();
				$extras->document = $document;
				$extras->ecli = $row['ecli'];
				$extras->decisionType = $row['decision_type'];
			} elseif ($courtId == Court::TYPE_NSS) {
				$extras = $this->documentService->findExtraByOrderNumber($row['order_number']);
				/* exists extra document but wasn't found record with main document */
				if ($entity == null && $extras != null) {
					$output .= sprintf("Warning: extra document with order number '%s' already found in database.", $extras->getValue("orderNumber"));
					continue;
				}
				$extras = ($update) ? $toUpdate : new DocumentSupremeAdministrativeCourt();
				$this->setSupremeAdministrativeCourtDocument($document, $extras, $row);
				if ($document->case) {
					$this->updateCauseDate($document->case, $row);
				}
			} elseif ($courtId == Court::TYPE_US) {
				$extras = ($update) ? $toUpdate : new DocumentConstitutionalCourt();
				$this->setConstitutionalCourtDocument($document, $extras, $row);
				if ($document->case) {
					$this->updateCauseDate($document->case, $row);
				}
			}
			// Store to database
			$this->documentService->insert($document, $extras);
			$imported++;
			// Copy document file into destination folder
			$destinationPath = $destinationDir . $row['local_path'];
			if (!$overwrite && file_exists($destinationPath)) {
				$temp = sprintf("Warning: Skipping file %s as it already exists in %s.", $row['local_path'], $destinationDirRelative);
				$output .= $temp . "\n";
				$consoleOutput->writeln($temp);
				continue;
			}
			copy($directory . '/documents/' . $row['local_path'], $destinationPath); // copy immediately - better to have not referenced files than documents in database without their files.
		}
		return [$imported, $duplicated];
	}

	private function getCourt($courtId)
	{
		if (!$this->court || $this->court->id != $courtId) {
			$this->court = $this->courtService->getById($courtId);
		}
		return $this->court;
	}

	/**
	 * Update case dates (proposition_date, decision_date) from document's metadata
	 * only for ÚS and NSS
	 */
	private function updateCauseDate(Cause $case, $row)
	{
		$updated = false;
		if (!$case->decisionDate && $row['decision_date']) {
			$case->decisionDate = $row['decision_date'];
			$updated = true;
		}
		if (!$case->propositionDate) {
			$case->propositionDate = ($this->court->id == 1) ? $row['proposition_date'] : $row['filing_date'];
			$updated = true;
		}
		if ($updated) {
			$this->causeService->save($case);
		}
	}

	private function getCourtDirectory($court, $projectRelative = false)
	{
		$baseDir = static::DOCUMENTS_DIRECTORY;
		if ($projectRelative) {
			$baseDir = static::DOCUMENTS_DIRECTORY_PROJECT_RELATIVE;
		}
		switch ($court) {
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
		switch ($court) {
			case Court::TYPE_NS:
				return [
					'court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'ecli',
					'decision_type'];
			case Court::TYPE_NSS:
				return [
					'court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path',
					'decision_type', 'decision', 'order_number', 'sides', 'complaint', 'prejudicate', 'case_year'];
			case Court::TYPE_US:
				return [
					'court_name', 'record_id', 'registry_mark', 'decision_date', 'case_year', 'web_path',
					'local_path', 'form_decision', 'decision_result', 'ecli', 'paralel_reference_laws',
					'paralel_reference_judgements', 'popular_title', 'delivery_date', 'filing_date', 'publication_date',
					'proceedings_type', 'importance', 'proposer', 'institution_concerned', 'justice_rapporteur',
					'contested_act', 'concerned_laws', 'concerned_other', 'dissenting_opinion', 'proceedings_subject',
					'subject_index', 'ruling_language', 'note', 'names'];
		}
	}

	private function getYear($row)
	{
		// Explicit year
		if (isset($row['case_year'])) {
			return (int)$row['case_year'];
		}
		// Guess from registry mark
		return Helpers::determineYear($row['registry_mark']);
	}

	/**
	 * @param $document
	 * @param $extras
	 * @param $row
	 * @throws \Nette\Utils\JsonException
	 */
	private function setSupremeAdministrativeCourtDocument($document, $extras, $row): void
	{
		$extras->document = $document;
		$extras->orderNumber = $row['order_number'];
		$extras->decision = $row['decision'];
		$extras->decisionType = $row['decision_type'];
		// More metadata
		$extras->complaint = ($row['complaint'] != "") ? $row['complaint'] : null;
		$extras->sides = ($row['sides'] != "") ? Json::decode($row['sides'], true) : null;
		$extras->prejudicate = ($row['prejudicate'] != "") ? Json::decode($row['prejudicate'], true) : null;
	}

	/**
	 * @param $document
	 * @param $extras
	 * @param $row
	 * @throws \Nette\Utils\JsonException
	 */
	private function setConstitutionalCourtDocument($document, $extras, $row): void
	{
		$extras->document = $document;
		$extras->ecli = $row['ecli'];
		$extras->formDecision = $row['form_decision'];
		$extras->decisionResult = $row['decision_result'];
		// More metadata
		$extras->paralelReferenceLaws = ($row['paralel_reference_laws'] != '') ? $row['paralel_reference_laws'] : null;
		$extras->paralelReferenceJudgements = ($row['paralel_reference_judgements'] != '') ? $row['paralel_reference_judgements'] : null;
		$extras->popularTitle = ($row['popular_title'] != '') ? $row['popular_title'] : null;
		$extras->deliveryDate = ($row['delivery_date'] != '') ? new DateTime($row['delivery_date']) : null;
		$extras->decisionDate = ($row['decision_date'] != '') ? new DateTime($row['decision_date']) : null;
		$extras->filingDate = ($row['filing_date'] != '') ? new DateTime($row['filing_date']) : null;
		$extras->publicationDate = ($row['publication_date'] != '') ? new DateTime($row['publication_date']) : null;
		$extras->proceedingsType = ($row['proceedings_type'] != '') ? $row['proceedings_type'] : null;
		$extras->importance = ($row['importance'] != '') ? $row['importance'] : null;
		$extras->proposer = ($row['proposer'] != '') ? Json::decode($row['proposer'], true) : null;
		$extras->institutionConcerned = ($row['institution_concerned'] != '') ? Json::decode($row['institution_concerned'], true) : null;
		$extras->justiceRapporteur = ($row['justice_rapporteur'] != '') ? $row['justice_rapporteur'] : null;
		$extras->contestedAct = ($row['contested_act'] != '') ? Json::decode($row['contested_act'], true) : null;
		$extras->concernedLaws = ($row['concerned_laws'] != '') ? Json::decode($row['concerned_laws'], true) : null;
		$extras->concernedOther = ($row['concerned_other'] != '') ? Json::decode($row['concerned_other'], true) : null;
		$extras->dissentingOpinion = ($row['dissenting_opinion'] != '') ? Json::decode($row['dissenting_opinion'], true) : null;
		$extras->proceedingsSubject = ($row['proceedings_subject'] != '') ? Json::decode($row['proceedings_subject'], true | JSON_NUMERIC_CHECK) : null;
		$extras->subjectIndex = ($row['subject_index'] != '') ? Json::decode($row['subject_index'], true) : null;
		$extras->rulingLanguage = ($row['ruling_language'] != '') ? $row['ruling_language'] : null;
		$extras->note = ($row['note'] != '') ? $row['note'] : null;
		$extras->names = ($row['names'] != '') ? Json::decode($row['names'], true) : null;
	}
}
