<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 21.11.2016
 * Time: 16:28
 */

namespace App\Commands;


use App\Enums\CaseResult;
use App\Enums\TaggingStatus;
use App\Enums\Court;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Services\nssDocumentService;
use App\Model\Services\CaseResultService;
use App\Model\Services\usDocumentService;
use App\Utils\JobCommand;
use App\Utils\Validators;
use App\Model\Taggings\TaggingCaseResult;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagResultsNSS extends Command
{
    use JobCommand;
    use Validators;

    /** @var CauseService @inject */
    public $causeService;

    /** @var DocumentService @inject */
    public $documentService;

    /** @var nssDocumentService @inject */
    public $nssDocumentService;

    /** @var usDocumentService @inject */
    public $usDocumentService;

    /** @var CaseResultService @inject */
    public $caseResultService;

    protected function configure()
    {
        $this->setName('app:tagNSS')
            ->setDescription('Tag results for NSS.');
    }

    protected function execute(InputInterface $input, OutputInterface $consoleOutput)
    {
        $this->prepare();
        $consoleOutput->writeln("'".$this->user->username."'");
        $cases = $this->causeService->findAll();
        $consoleOutput->writeln("Vypis");
        //var_dump($documents);
        $court = 'us';//$input->getArgument(static::ARGUMENT_COURT);
        $courtId = Court::$types[$court];
        $i = 0;
        foreach ($cases as $case) {
            $documents = $this->documentService->findByCaseId($case->id);
            $find = FALSE;
            $onlyOne = TRUE;

            foreach ($documents as $document) {
                if ($document->court->id == $courtId) {
                    $consoleOutput->writeln(count($documents));
                    if (!$find) {
                        $extra = $this->findExtraByDocumentId($courtId,$document->id);
                        if ($extra != null) {
                            $type = $extra->decisionType;
                            $decision = $extra->decision;
                            if($onlyOne) {
                                $consoleOutput->writeln($case->id . " " . $case->registrySign . "(" . count($documents) . ")");
                                $onlyOne = FALSE;
                            }

                            if (
                                Strings::contains($decision, "zastaveno") or
                                Strings::contains($decision, "odmítnuto") or
                                strtolower($type) == "rozsudek"
                            ) {

                                $consoleOutput->write("\t->" . $document->id . ", " . $document->decisionDate);
                                $caseResult = $this->computeTagResult($type,$decision);
                                $consoleOutput->writeln(": " . $type . ", " . $decision . " => ".$caseResult);
                                $find = TRUE;
                                //Prepare for insert
                                $result = new TaggingCaseResult();
                                $result->document = $document;
                                $result->caseResult = $caseResult;
                                $result->status = TaggingStatus::STATUS_PROCESSED;
                                $result->debug = $type.", ".$decision;
                                $result->jobRun = $this->jobRun;
                                $result->insertedBy = $this->user;
                                $result->isFinal = FALSE;

                                //Store to DB
                                /* @var TaggingCaseResult $entity*/
                                $entity = $this->caseResultService->findByDocument($document);
                                if($entity)
                                    continue;
                                else
                                    $this->caseResultService->insert($result);


                                //break;
                            } else
                                //$consoleOutput->writeln(Strings::contains("odmítnuto", "odmítnuto"));
                                $consoleOutput->writeln("\t->" . $document->id . ", " . $document->decisionDate.", ".$document->case->id.": " . $type . ", " . $decision);

                        }
                    }
                    $i++;
                }

            }
        }
        $this->caseResultService->flush();
        echo $i;
    }

    protected function computeTagResult(string $type, string $decision)
    {
        if (strtolower($type) == "rozsudek")
            return CaseResult::RESULT_POSITIVE;
        elseif (!Strings::contains($decision, "odmítnuto") and Strings::contains($decision, "zastaveno"))
            return CaseResult::RESULT_NEUTRAL;
        elseif (Strings::contains($decision, "odmítnuto"))
            return CaseResult::RESULT_NEGATIVE;
        else
            return CaseResult::RESULT_UNKNOWN;
    }

    protected function findExtraByDocumentId($courtId, $documentId)
    {
        if ($courtId == Court::TYPE_NSS)
            return $this->nssDocumentService->findByDocumentId($documentId);
        elseif ($courtId == Court::TYPE_US)
            return $this->usDocumentService->findByDocumentId($documentId);
        else
            return null;

    }
}