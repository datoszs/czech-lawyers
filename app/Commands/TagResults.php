<?php

namespace App\Commands;

use App\Enums\CaseResult;
use App\Enums\Court;
use App\Enums\TaggingStatus;
use App\Model\Orm;
use App\Model\Services\CourtService;
use App\Model\Services\TaggingService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Taggings\TaggingCaseResult;
use App\Model\Cause\Cause;
use App\Model\Taggings\TaggingCaseSuccess;
use App\Utils\JobCommand;
use App\Utils\TemplateFilters;
use Nette\Utils\Strings;
use Nette\Utils\Json;
use Nextras\Orm\Model\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function array_column;
use function array_unique;
use function explode;
use function gc_collect_cycles;
use function implode;
use function sprintf;
use function trim;

class TagResults extends Command
{
	use JobCommand;
	const ARGUMENT_COURT = 'court';
	const SUCCESS_TAGGING = 'success-tagging';
	const SUCCESS_TAGGING_SHORTCUT = 's';
	const DECISION_RESULT_STOPPED = 'zastaveno';
	const DECISION_RESULT_REJECTED = 'odmítnuto';
	const DECISION_RESULT_CANCELED = 'zrušeno';
	const DECISION_RESULT_RETURNED = 'vráceno';
	const DECISION_RESULT_GRANTED = 'vyhověno';
	const FORM_US_0 = 'nález';
	const FORM_US_1 = 'usnesení';
	const FORM_NSS = 'rozsudek';
	const DIFFERENT = 666;

	protected $processed = 0;
	protected $ignored = 0;
	protected $failed = 0;
	protected $empty = 0;
	protected $updated = 0;
	protected $different = 0;

	/** @var CourtService @inject */
	public $courtService;

	/** @var CauseService @inject */
	public $causeService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var Orm @inject */
	public $orm;

	protected function configure()
	{
		$this->setName('app:tag-results')
			->setDescription('Tag results for NSS and ÚS.')
			->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				'Identificator of court')
			->addOption(
				static::SUCCESS_TAGGING,
				static::SUCCESS_TAGGING_SHORTCUT,
				InputOption::VALUE_NONE,
				'Use second tagging criteria? Default is false.');
	}

	public function computeCaseResult($courtId, $type, $decision)
	{
		$specificForm = NULL;
		if (Court::TYPE_NSS == $courtId)
			$specificForm = static::FORM_NSS;
		elseif (Court::TYPE_US == $courtId)
			$specificForm = static::FORM_US_0;

		if ($type == $specificForm)
			return CaseResult::RESULT_POSITIVE;
		elseif (
			!Strings::contains($decision, static::DECISION_RESULT_REJECTED) and
			Strings::contains($decision, static::DECISION_RESULT_STOPPED)
		)
			return CaseResult::RESULT_NEUTRAL;
		elseif (Strings::contains($decision, static::DECISION_RESULT_REJECTED))
			return CaseResult::RESULT_NEGATIVE;
		else
			return CaseResult::RESULT_UNKNOWN;
	}

	public function computeCaseSuccess($courtId, $type, $decision)
	{
		if (Court::TYPE_NSS == $courtId) {
			return $this->computeCaseSuccessForNSS($type, $decision);
		} elseif (Court::TYPE_US == $courtId) {
			return $this->computeCaseSuccessForUS($type, $decision);
		}
	}

	protected function getTypeAndDecision($courtId, $extra)
	{
		if (Court::TYPE_NSS == $courtId)
			return [$extra->decisionType, $extra->decision];
		elseif (Court::TYPE_US == $courtId)
			return [$extra->formDecision, $extra->decisionResult];
		return [null, null];
	}

	protected function makeStatistic($status, $action)
	{
		if ($status == NULL && !$action) {
			$this->empty++;
		}
		if (!$action) {
			switch ($status) {
				case TaggingStatus::STATUS_PROCESSED:
					{
						$this->processed++;
						break;
					}
				case TaggingStatus::STATUS_IGNORED:
					{
						$this->ignored++;
						break;
					}
				case TaggingStatus::STATUS_FAILED:
					{
						$this->failed++;
						break;
					}
				case (static::DIFFERENT):
					{
						$this->different++;
						break;
					}
			}
		} else {
			return
				"Processed: {$this->processed},
                Ignored: {$this->ignored},
                Failed: {$this->failed},
                Empty: {$this->empty},
                Different: {$this->different}";
		}
	}

	public function isRelevant($courtId, $type, $decision)
	{
		if (Court::TYPE_NSS == $courtId) {
			if (
				Strings::contains($decision, static::DECISION_RESULT_STOPPED) ||
				Strings::contains($decision, static::DECISION_RESULT_REJECTED) ||
				$type == static::FORM_NSS
			) {
				return true;
			}
		} elseif (Court::TYPE_US == $courtId) {
			if (
				(
					Strings::contains($decision, static::DECISION_RESULT_STOPPED) ||
					Strings::contains($decision, static::DECISION_RESULT_REJECTED) &&
					$type == static::FORM_US_1
				) || $type == static::FORM_US_0
			) {
				return true;
			}
		}
		return false;
	}

	protected function processDocument($documents, $courtId, OutputInterface $consoleOutput, $successTagging)
	{
		$find = FALSE;
		$onlyOne = TRUE;
		$document = $debug = null;
		$status = CaseResult::RESULT_UNKNOWN;
		$caseResult = CaseResult::RESULT_UNKNOWN;

		foreach ($documents as $document) {
			if (!$find) {
				$extra = $this->documentService->findExtraData($document);
				if ($extra != null) {
					list($type, $decision) = $this->getTypeAndDecision($courtId, $extra);
					$debug = $type . ", " . $decision;
					if ($onlyOne) {
						$cause = $document->case;
						$onlyOne = FALSE;
					}

					if ($this->isRelevant($courtId, Strings::lower($type), $decision)) {
						if ($successTagging) {
							$caseResult = $this->computeCaseSuccess($courtId, Strings::lower($type), $decision);
						} else {
							$caseResult = $this->computeCaseResult($courtId, Strings::lower($type), $decision);
						}
						$status = TaggingStatus::STATUS_PROCESSED;
						$find = TRUE;
					} else { // find another relevant document
						$status = TaggingStatus::STATUS_IGNORED;
						continue;
					}
				} else { // extra information not found
					echo "FAILED";
					$status = TaggingStatus::STATUS_FAILED;
				}
			}
		}
		return [$document, $caseResult, $debug, $status];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$successTagging = $input->getOption(static::SUCCESS_TAGGING);
		$output = null;
		$this->prepare();
		$consoleOutput->writeln($court);
		$courtId = Court::$types[$court];
		$jobId = $this->job->id;
		$jobRunId = $this->jobRun->id;
		$userId = $this->user->id;

		$nextIteration = true;
		$offset = 0;
		do {
			$courtEntity = $this->courtService->getById($courtId);
			if ($successTagging) {
				$causes = $this->causeService->findForSuccessTagging($courtEntity)->limitBy(1000, $offset);
			} else {
				$causes = $this->causeService->findForResultTagging($courtEntity)->limitBy(1000, $offset);
			}

			if ($causes->countStored() === 0) {
				$nextIteration = false;
			}

			foreach ($causes as $cause) {
				$documents = $this->documentService->findByCaseId($cause->id);

				if ($documents == null) {
					$this->makeStatistic(null, false);
					continue;
				}
				list($document, $caseResult, $debug, $status) = $this->processDocument($documents, $courtId, $consoleOutput, $successTagging);
				if ($document == null) {
					$this->makeStatistic($status, false);
					continue;
				}

				if ($cause->officialData && $cause->court->id == Court::TYPE_NSS) {
					$json_input = implode("; ", array_unique(array_column($cause->officialData, "result")));
					$part = trim(explode(", ", $debug)[1]);
					if (!Strings::contains($json_input, $part)) {
						$this->makeStatistic(static::DIFFERENT, false);
						$message = TemplateFilters::formatRegistryMark($cause->registrySign) . " - Court: '" . $json_input . "'; Web: '" . $debug . "'\n";
						$consoleOutput->write($message);
						$output .= $message;
					}
				}

				if ($document) {
					if ($cause->decisionDate == null && $document->decisionDate) {
						$cause->decisionDate = $document->decisionDate;
						$this->causeService->save($cause);
					}
				}
				$result = $successTagging ? new TaggingCaseSuccess() : new TaggingCaseResult();
				if ($successTagging) {
					$result->caseSuccess = $caseResult;
				} else {
					$result->caseResult = $caseResult;
				}

				$result->debug = $debug;
				$result->document = $document;
				$result->case = $cause;
				$result->status = $status;
				$result->isFinal = false;
				$result->insertedBy = $this->user;
				$result->jobRun = $this->jobRun;
				$output .= sprintf("Tagging case result for case [%s] of [%s]\n", TemplateFilters::formatRegistryMark($cause->registrySign), $cause->court->name);
				if ($successTagging) {
					$entity = $this->taggingService->findCaseSuccessByDocument($document);
				} else {
					$entity = $this->taggingService->findByDocument($document);
				}

				if ($entity) {
					if ($successTagging) {
						if ($this->taggingService->persistCaseSuccessIfDiffers($result)) {
							$this->updated++;
						}
					} elseif ($this->taggingService->persistCaseResultIfDiffers($result)) {
						$this->updated++;
					}
				} else {
					$this->makeStatistic($status, false);
					$this->taggingService->insert($result);
				}
				// Flush immediately
				$this->taggingService->flush();
			}

			// Clear identity map to allow proper GC
			$this->orm->clearIdentityMapAndCaches(Model::I_KNOW_WHAT_I_AM_DOING);
			gc_collect_cycles();
			$offset += 1000;

			// Reload data into new identity map
			$this->job = $this->jobService->get($jobId);
			$this->jobRun = $this->orm->jobRuns->getById($jobRunId);
			$this->user = $this->userService->get($userId);
			$this->auditing->setCurrentUser($this->user);
		} while ($nextIteration);

		$message = $this->makeStatistic(null, true) . " (" . strtoupper($court) . ")";
		$this->finalize(0, $output, $message);
		return 0;
	}

	private function computeCaseSuccessForNSS($type, $decision): string
	{
		if ($type == static::FORM_NSS) {
			if (
				Strings::contains($decision, static::DECISION_RESULT_CANCELED) ||
				Strings::contains($decision, static::DECISION_RESULT_RETURNED)) {
				return CaseResult::RESULT_POSITIVE;
			} elseif (
				!Strings::contains($decision, static::DECISION_RESULT_CANCELED) &&
				!Strings::contains($decision, static::DECISION_RESULT_RETURNED)) {
				return CaseResult::RESULT_NEGATIVE;
			}
		} elseif (
			!Strings::contains($decision, static::DECISION_RESULT_REJECTED) &&
			Strings::contains($decision, static::DECISION_RESULT_STOPPED)
		) {
			return CaseResult::RESULT_NEUTRAL;
		} elseif (Strings::contains($decision, static::DECISION_RESULT_REJECTED)) {
			return CaseResult::RESULT_NEGATIVE;
		} else {
			return CaseResult::RESULT_UNKNOWN;
		}
	}

	private function computeCaseSuccessForUS($type, $decision): string
	{
		if ($type == static::FORM_US_0) {
			if (Strings::contains($decision, static::DECISION_RESULT_GRANTED)) {
				return CaseResult::RESULT_POSITIVE;
			} else {
				return CaseResult::RESULT_NEGATIVE;
			}
		} elseif (
			!Strings::contains($decision, static::DECISION_RESULT_REJECTED) &&
			Strings::contains($decision, static::DECISION_RESULT_STOPPED)
		) {
			return CaseResult::RESULT_NEUTRAL;
		} elseif (Strings::contains($decision, static::DECISION_RESULT_REJECTED)) {
			return CaseResult::RESULT_NEGATIVE;
		} else {
			return CaseResult::RESULT_UNKNOWN;
		}
	}
}

