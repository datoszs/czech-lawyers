<?php
namespace App\Commands;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ITransactionLogger;
use App\Enums\Court;
use App\Model\Cause\Cause;
use App\Model\Court\Court as CourtEntity;
use App\Model\Disputes\Dispute;
use App\Model\Documents\Document;
use App\Model\Orm;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Model\Services\DisputationService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\Normalize;
use App\Utils\Validators;
use App\Utils\JobCommand;
use Nextras\Dbal\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is tool for unifying duplicated cases after a problem is found:
 * 0. Make database backup
 * 1. Fix registry mark normalization
 * 2. Ensure that preconditions of these scripts are fulfilled, typically by using ad-hoc analysis directly on database
 * 3. Run in dry mode
 * 4. Run in non-dry mode
 *
 * This tool does (check also the implementation):
 * 1. Fetch all cases of given court and iterate them one-by-one.
 * 2. Compute new registry mark and compare it with the old one, when no change case is skipped.
 * 3. Attempt to find case with the new registry mark (target case).
 *    Not found:
 *      a. Rename case
 *    Found:
 *      a. Transfer official data, proposition/decision dates from old to target case if empty on target and not-empty on old.
 *      b. Assign documents of old case to target case.
 *      c. Assign existing disputations of old case to target case.
 *      d. For each advocate tagging: recreate all taggings of old case in target case (as update is not allowed)
 *      e. For each results tagging: recreate all taggings of old case in target case (as update is not allowed)
 *      f. drop old case and all its taggings
 *
 * There cannot be a general-purpose utility as there are various things that can go wrong:
 * - Can create duplicity in documents (as it only union all existing documents)
 * - Can drop official data, proposition/decision dates when on both cases incomplete
 * - Can lead to invalid (temporary/permanent) tagging if there there is tagging which is new last and changes the result in inconsistent way
 *   (as it is probably done from inconsistent data), real problem is when such tagging is marked as final.
 *
 * Tagging should be executed afterwards (to fix the temporary inconsistency in data).
 *
 */
class CausaUnifier extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_COURT = 'court';
	const DRY_RUN = 'dry-run';
	const DRY_RUN_SHORTCUT = 'd';


	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_COURT = 1;

	/** @var CourtService @inject */
	public $courtService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var CauseService @inject */
	public $causeService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var DisputationService @inject */
	public $disputationService;

	/** @var CourtEntity */
	private $court;

	/** @var Orm @inject */
	public $orm;

	/** @var Connection @inject */
	public $connection;

	protected function configure()
	{
		$this->setName('app:unify-cases')
			->setDescription('[DANGEROUS] Walks through all cases of given court and unify them according to newly normalized registry marks which fixes a bunch of things. This is powerful yet dangerous, read the implementation.')
			->addOption(
				static::DRY_RUN,
				static::DRY_RUN_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should write operations be skipped?'
			)->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				sprintf('Court which is imported, available values are: %s', implode(', ', array_keys(Court::$types)))
			);
	}

	/**
	 * Expects validate directory with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param string $output Output to be stored
	 * @param int $courtId ID of court
	 * @param bool $dryRun whether the write operations should be performed
	 * @param ITransactionLogger $transactionLogger
	 * @return array where first is number of imported and second number of duplicated items.
	 * @internal param string $directory
	 * @internal param bool $overwrite whether the files should be overwritten
	 */
	public function processCourt(OutputInterface $consoleOutput, &$output, $courtId, $dryRun, ITransactionLogger $transactionLogger)
	{
		$court = $this->getCourt($courtId);
		$merged = 0;
		$renamed = 0;
		$ignored = 0;

		$cases = $this->causeService->findFromCourt($court);
		/** @var Cause $case */
		foreach ($cases as $case)
		{
			// Prepare new registry mark (newly normalized)
			$oldRegistryMark = $case->registrySign;
			$newRegistryMark = Normalize::registryMark($case->registrySign);
			if ($newRegistryMark === $case->registrySign) {
				$ignored++;
				continue;
			}
			// Check if there exists with same
			$newCase = $this->causeService->find($newRegistryMark);
			if (!$newCase) { // Just update
				$case->registrySign = $newRegistryMark;
				$this->causeService->save($case);
				$output .= "Renamed case {$oldRegistryMark} to {$newRegistryMark}.\n";
				$consoleOutput->write("Renamed case {$oldRegistryMark} to {$newRegistryMark}.\n");
				$renamed++;
			} else { // Merge
				// Transfer data from old case to new
				if ($case->officialData && !$newCase->officialData) {
					$newCase->officialData = $case->officialData;
				}
				if ($case->propositionDate && !$newCase->propositionDate) {
					$newCase->propositionDate = $case->propositionDate;
				}
				if ($case->decisionDate && !$newCase->decisionDate) {
					$newCase->decisionDate = $case->decisionDate;
				}
				$this->causeService->save($newCase);
				$merged++;
				// Migrate documents
				/** @var Document $document */
				foreach ($this->documentService->findByCaseId($case->id) as $document) {
					$document->case = $newCase;
					$this->documentService->save($document);
				}
				// Migrate case disputations
				/** @var Dispute $disputation */
				foreach ($this->disputationService->findByCase($case) as $disputation) {
					$disputation->case = $newCase;
					$this->disputationService->save($disputation);
				}
				// Migrate advocates tagging
				// Rationale: if there are any cases to be merged together, they should be the same, therefore, leaving all tagging in place should workwhether the master is right
				/** @var TaggingAdvocate $tagging */
				foreach ($this->taggingService->findAdvocateTaggingsByCase($case) as $tagging) {
					$newTagging = new TaggingAdvocate();
					$newTagging->case = $newCase;
					$newTagging->document = $tagging->document;
					$newTagging->advocate = $tagging->advocate;
					$newTagging->status = $tagging->status;
					$newTagging->isFinal = $tagging->isFinal;
					$newTagging->debug = $tagging->debug;
					$newTagging->inserted = $tagging->inserted;
					$newTagging->insertedBy = $tagging->insertedBy;
					$newTagging->jobRun = $tagging->jobRun;

					$this->taggingService->persist($newTagging);
					$this->taggingService->remove($tagging);
					$transactionLogger->logCreate(AuditedSubject::CASE_TAGGING, "Migrate advocate tagging with ID [{$tagging->id}] of case [{$oldRegistryMark}] to case [{$newRegistryMark}] with ID [{$newCase->id}] in new row with ID [{$newTagging->id}].", AuditedReason::FIXUP);
				}
				// Migrate case result tagging
				/** @var TaggingCaseResult $tagging */
				foreach ($this->taggingService->findCaseResultTaggingsByCase($case) as $tagging) {
					$newTagging = new TaggingCaseResult();
					$newTagging->case = $newCase;
					$newTagging->document = $tagging->document;
					$newTagging->caseResult = $tagging->caseResult;
					$newTagging->status = $tagging->status;
					$newTagging->isFinal = $tagging->isFinal;
					$newTagging->debug = $tagging->debug;
					$newTagging->inserted = $tagging->inserted;
					$newTagging->insertedBy = $tagging->insertedBy;
					$newTagging->jobRun = $tagging->jobRun;
					$this->taggingService->persist($newTagging);
					$this->taggingService->remove($tagging);
					$transactionLogger->logCreate(AuditedSubject::CASE_TAGGING, "Migrate case result tagging with ID [{$tagging->id}] of case [{$oldRegistryMark}] to case [{$newRegistryMark}] with ID [{$newCase->id}] in new row with ID [{$newTagging->id}].", AuditedReason::FIXUP);
				}
				// Remove original entity
				$this->causeService->remove($case);
				$output .= "Merged case {$oldRegistryMark} to {$newRegistryMark}.\n";
				$consoleOutput->write("Merged case {$oldRegistryMark} to {$newRegistryMark}.\n");
			}
		}

		return [$merged, $renamed, $ignored];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$dryRun = $input->getOption(static::DRY_RUN);
		$code = 0;
		$output = null;
		$message = null;
		if (!isset(Court::$types[$court])) {
			$message = 'Error: The given court is not valid value.';
			$code = static::RETURN_CODE_INVALID_COURT;
			$consoleOutput->writeln($message);
		} else {

			$courtEntity = $this->getCourt(Court::$types[$court]);
			$temp = sprintf("Court: %s\n", $courtEntity->name);
			$output .= $temp;
			$consoleOutput->write($temp);
			$transactionLogger = $this->auditing->createTransactionLogger();

			// import to db
			list($merged, $renamed, $ignored) = $this->processCourt($consoleOutput, $output, Court::$types[$court], $dryRun, $transactionLogger);
			if (!$dryRun && ($merged > 0 || $renamed > 0)) {
				$this->causeService->flush();
				$transactionLogger->commit();
			} else {
				$this->connection->rollbackTransaction();
			}
			if ($dryRun) {
				$message = "Dry run: Renamed {$renamed} cases, merged {$merged} cases and ignored {$ignored}\n";
			} else {
				$message = "Renamed {$renamed} cases, merged {$merged} cases and ignored {$ignored}\n";
			}
			$output .= $message;
			$consoleOutput->write($message);
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}

	private function getCourt($courtId)
	{
		if (!$this->court || $this->court->id != $courtId) {
			$this->court = $this->courtService->getById($courtId);
		}
		return $this->court;
	}
}
