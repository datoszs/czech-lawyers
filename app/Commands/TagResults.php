<?php
/**
 * Created by IntelliJ IDEA.
 * User: Xorel
 * Date: 07.12.2016
 * Time: 20:33
 */

namespace App\Commands;


use App\Enums\CaseResult;
use App\Enums\Court;
use App\Enums\TaggingStatus;
use App\Model\Services\CourtService;
use App\Model\Services\TaggingService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\JobCommand;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagResults extends Command
{
    use JobCommand;
    const ARGUMENT_COURT = 'court';
    const DECISION_RESULT_NEUTRAL = "zastaveno";
    const DECISION_RESULT_NEGATIVE = "odmítnuto";
    const FORM_US_0 = "nález";
    const FORM_US_1 = "usnesení";
    const FORM_NSS = "rozsudek";

    protected $processed = 0;
    protected $ignored = 0;
    protected $failed = 0;
    protected $fuzzy = 0;
    protected $empty = 0;
    protected $updated = 0;

    /** @var CourtService @inject */
    public $courtService;

    /** @var CauseService @inject */
    public $causeService;

    /** @var DocumentService @inject */
    public $documentService;

    /** @var TaggingService @inject */
    public $taggingService;

    protected function configure()
    {
        $this->setName('app:tag-results')
            ->setDescription('Tag results for NSS and ÚS.')
            ->addArgument(
                static::ARGUMENT_COURT,
                InputArgument::REQUIRED,
                'Identificator of court');
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
            !Strings::contains($decision, static::DECISION_RESULT_NEGATIVE) and
            Strings::contains($decision, static::DECISION_RESULT_NEUTRAL)
        )
            return CaseResult::RESULT_NEUTRAL;
        elseif (Strings::contains($decision, static::DECISION_RESULT_NEGATIVE))
            return CaseResult::RESULT_NEGATIVE;
        else
            return CaseResult::RESULT_UNKNOWN;

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
                case TaggingStatus::STATUS_PROCESSED: {
                    $this->processed++;
                    break;
                }
                case TaggingStatus::STATUS_IGNORED: {
                    $this->ignored++;
                    break;
                }
                case TaggingStatus::STATUS_FAILED: {
                    $this->failed++;
                    break;
                }
                case TaggingStatus::STATUS_FUZZY: {
                    $this->fuzzy++;
                    break;
                }
            }
        } else {
            return "Processed: {$this->processed}, Ignored: {$this->ignored}, Failed: {$this->failed}, Fuzzy: {$this->fuzzy}, Empty: {$this->empty}, Updated: {$this->updated}";
        }

    }

    public function isRelevant($courtId, $type, $decision)
    {
        if (Court::TYPE_NSS == $courtId) {
            if (
                Strings::contains($decision, static::DECISION_RESULT_NEUTRAL) ||
                Strings::contains($decision, static::DECISION_RESULT_NEGATIVE) ||
                $type == static::FORM_NSS
            )
                return true;
        } elseif (Court::TYPE_US == $courtId) {
            /*if(!mb_detect_encoding($type,"UTF-8",true)) {
                $current_encoding = mb_detect_encoding($type);
                echo "není to UTF-8, ale je to ".$current_encoding."\n";
                $type = mb_convert_encoding($type,"UTF-8");//utf8_encode($type);
                echo $type;
            }*/

            if (
                (
                    Strings::contains($decision, static::DECISION_RESULT_NEUTRAL) ||
                    Strings::contains($decision, static::DECISION_RESULT_NEGATIVE) &&
                    $type == static::FORM_US_1
                ) || $type == static::FORM_US_0
            )
                return true;
        }
        return false;
    }

    protected function processDocument($documents, $courtId, OutputInterface $consoleOutput)
    {
        $find = FALSE;
        $onlyOne = TRUE;
        $document = $debug = null;
        $status = CaseResult::RESULT_UNKNOWN;
        $caseResult = CaseResult::RESULT_UNKNOWN;

        foreach ($documents as $document) {
            //$consoleOutput->writeln("Documents: ".count($documents));
            if (!$find) {
                $extra = $this->documentService->findExtraData($document);
                if ($extra != null) {
                    list($type, $decision) = $this->getTypeAndDecision($courtId, $extra);
                    //$consoleOutput->writeln($type.", ".$decision);
                    $debug = $type . ", " . $decision;
                    if ($onlyOne) {
                        $cause = $document->case;
                        //$consoleOutput->writeln($cause->id . " " . $cause->registrySign . "(" . count($documents) . ")");
                        $onlyOne = FALSE;
                    }

                    if ($this->isRelevant($courtId, mb_strtolower($type), $decision)) {
                        $caseResult = $this->computeCaseResult($courtId, mb_strtolower($type), $decision);
                        $status = TaggingStatus::STATUS_PROCESSED;
                        $find = TRUE;
                    } else { // find another relevant document
                        //$consoleOutput->writeln("\t" . $type . ", " . $decision);
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
        $consoleOutput->writeln("court: " . $court);
        $this->prepare();
        $courtId = Court::$types[$court];
        $causes = $this->causeService->findForTagging($this->courtService->getById($courtId));
        //$consoleOutput->writeln($courtId . " " . count($causes));
        foreach ($causes as $cause) {
            $documents = $this->documentService->findByCaseId($cause->id);
            //$consoleOutput->writeln($cause->id . " " . $cause->registrySign . "(" . count($documents) . ")");
            if ($documents == null) {
                $this->makeStatistic(null,false);
                continue;
            }
            list($document, $caseResult, $debug, $status) = $this->processDocument($documents, $courtId, $consoleOutput);
            if ($document == null) {
                $this->makeStatistic($status, false);
                continue;
            }

            $result = new TaggingCaseResult();
            $result->caseResult = $caseResult;
            $result->debug = $debug;
            $result->document = $document;
            $result->case = $cause;
            $result->status = $status;
            $result->isFinal = false;
            $result->insertedBy = $this->user;
            $result->jobRun = $this->jobRun;

            $entity = $this->taggingService->findByDocument($document);
            if ($entity) {
                if ($this->taggingService->persistCaseResultIfDiffers($result)) {
                    $this->updated++;
                }
            } else {
                $this->makeStatistic($status, false);
                $this->taggingService->insert($result);
            }

        }
        $this->taggingService->flush();
        $consoleOutput->writeln($this->makeStatistic(null, true));
    }


}