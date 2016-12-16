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
use App\Model\Services\TaggingService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Taggings\TaggingCaseResult;
use app\Utils\JobCommand;
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
            return "Processed: {$this->processed}, Ignored: {$this->ignored}, Failed: {$this->failed}, Fuzzy: {$this->fuzzy}";
        }

    }

    public function isRelevant($courtId, $type, $decision)
    {
        if (Court::TYPE_NSS == $courtId) {
            if (
                Strings::contains($decision, static::DECISION_RESULT_NEUTRAL) or
                Strings::contains($decision, static::DECISION_RESULT_NEGATIVE) or
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
                    Strings::contains($decision, static::DECISION_RESULT_NEUTRAL) or
                    Strings::contains($decision, static::DECISION_RESULT_NEGATIVE) and
                    $type == static::FORM_US_1
                ) or $type == static::FORM_US_0
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
            if ($document->court->id == $courtId) {
                //$consoleOutput->writeln("Documents: ".count($documents));
                if (!$find) {
                    $extra = $this->documentService->findExtra($courtId, $document->id);
                    if ($extra != null) {
                        list($type, $decision) = $this->getTypeAndDecision($courtId, $extra);
                        //$consoleOutput->writeln($type.", ".$decision);
                        $debug = $type . ", " . $decision;
                        if ($onlyOne) {
                            $cause = $document->case;
                            $consoleOutput->writeln($cause->id . " " . $cause->registrySign . "(" . count($documents) . ")");
                            $onlyOne = FALSE;
                        }

                        if ($this->isRelevant($courtId, mb_strtolower($type), $decision)) {
                            $caseResult = $this->computeCaseResult($courtId, mb_strtolower($type), $decision);
                            $status = TaggingStatus::STATUS_PROCESSED;
                            break;
                        } else { // find another relevant document
                            $consoleOutput->writeln("\t" . $type . ", " . $decision);
                            $status = TaggingStatus::STATUS_IGNORED;
                            continue;
                        }
                    } else { // extra information not found
                        $status = TaggingStatus::STATUS_FAILED;
                    }
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
        $causes = $this->causeService->findAll();
        $consoleOutput->writeln($courtId . " " . count($causes));
        foreach ($causes as $cause) {
            //$consoleOutput->writeln($cause->id);
            $documents = $this->documentService->findByCaseId($cause->id);
            //$consoleOutput->writeln(count($documents));
            list($document, $caseResult, $debug, $status) = $this->processDocument($documents, $courtId, $consoleOutput);
            if ($status == CaseResult::RESULT_UNKNOWN or $document->court->id != $courtId)
                continue;
            $result = new TaggingCaseResult();
            $result->caseResult = $caseResult;
            $result->debug = $debug;
            $result->document = $document;
            /* tady bude case_id, který se bude používat pro kontrolu existujícího a bude se porovnávat výsledek tagování */
            $result->status = $status;
            $result->isFinal = false;
            $result->insertedBy = $this->user;
            $result->jobRun = $this->jobRun;

            $this->makeStatistic($status, false);

            $entity = $this->taggingService->findByDocument($document);
            if ($entity)
                continue;
            else
                $this->taggingService->insert($result);
        }
        $this->taggingService->flush();
        $consoleOutput->writeln($this->makeStatistic(null, true));
    }


}