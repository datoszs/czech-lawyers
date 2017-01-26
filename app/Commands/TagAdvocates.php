<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 06.01.2017
 * Time: 20:41
 */

namespace App\Commands;

use App\Enums\Court;
use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Cause\Cause;
use App\Model\Orm;
use App\Model\Services\AdvocateService;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingCaseResult;
use App\Model\Taggings\TaggingAdvocate;
use app\Utils\JobCommand;
use Nette\Utils\ArrayList;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagAdvocates extends Command
{
    use JobCommand;
    const ARGUMENT_COURT = 'court';
    protected $court_id;
    protected $processed = 0;
    protected $ignored = 0;
    protected $failed = 0;
    protected $fuzzy = 0;
    protected $empty = 0;
    protected $updated = 0;
    protected $output = "";

    /** @var CauseService @inject */
    public $causeService;

    /** @var AdvocateService @inject */
    public $advocateService;

    /** @var TaggingService @inject */
    public $taggingService;

    /** @var Orm @inject */
    public $orm;

    protected function configure()
    {
        $this->setName('app:tag-advocates')
            ->setDescription('Tag advocates for NSS and ÚS.')
            ->addArgument(
                static::ARGUMENT_COURT,
                InputArgument::REQUIRED,
                'Identificator of court');
    }

    protected function prepareAndSave(Advocate $advocate,Cause $case, $debug) {
        $tagAdvocate = new TaggingAdvocate();
        $tagAdvocate->advocate = $advocate;
        $tagAdvocate->case = $case;
        $tagAdvocate->status = TaggingStatus::STATUS_PROCESSED;
        $tagAdvocate->isFinal = false;
        $tagAdvocate->document = null;
        $tagAdvocate->debug = $debug;
        $tagAdvocate->insertedBy = $this->user;
        $tagAdvocate->jobRun = $this->jobRun;

        $entity = $this->taggingService->findAdvocateTaggingsByCase($case);
        if(!$entity) {
            $this->output .= sprintf("Tagging advocate result for case [%s] of [%s]\n", $case->registrySign, $case->court->name);
            $this->taggingService->insert($tagAdvocate);
        }
    }
    /* primarne pro US */
    protected function processCase()
    {
        $court_id = $this->court_id;
        print($court_id . "\n");
        /* @var TaggingCaseResult $results */
        $results = $this->orm->taggingCaseResults->findTaggingResultsByCourt($court_id);
        //$results = $this->taggingService->findAll();
        $advocates = $this->orm->advocatesInfo->findUniqueNames();
        print("Ohodnocenych kauz: " . count($results) . "\n");
        //print($results);
        $shoda = 0;
        /* @var TaggingCaseResult $result */
        foreach ($results as $result) {
            $data = $result->case->officialData; // return last array in structure
            if (is_array($data) && count($data) == 1) {
                $data = end($data);
                switch ($court_id) {
                    case (Court::TYPE_US): {
                        $name = Strings::lower($data["name"]);
                        $surname = Strings::lower($data["surname"]);
                        $debug = $name." ".$surname;
                        if ($name == "" or $surname == "")
                            $this->empty++;
                            continue;
                        break;
                    }
                    case (Court::TYPE_NSS): {
                        $name = Strings::lower($data["names"]);
                        $debug = $name;
                        break;
                    }
                    default:
                        continue;
                }
            } else {continue;}

            foreach ($advocates as $advocate) {
                if(Strings::lower($advocate->name) == $name &&
                    Strings::lower($advocate->surname) == $surname
                ) {
                    print($result->case->registrySign . "\n");
                    printf("%s %s, %d, %s\n", $name, $surname, $advocate->advocate->id, end($advocate->email));
                    $this->prepareAndSave($advocate->advocate,$result->case,$debug);
                    $shoda++;
                    break;
                }
                if (strpos($name,Strings::lower($advocate->name." ".$advocate->surname))) {
                    print($result->case->registrySign . "\n");
                    printf("%s, %d, '%s', %s\n", $advocate->name." ".$advocate->surname, $advocate->advocate->id, $name, end($advocate->email));
                    $this->prepareAndSave($advocate->advocate,$result->case,$debug);
                    $shoda++;
                    break;
                }
            }
            if ($shoda % 100 == 0) {$this->taggingService->flush();}

        }
        $this->taggingService->flush();
        $this->output .= sprintf("\nNalezeno: %d shod; prázdných: %d; úspěšnost: %f%%",$shoda,$this->empty, ($shoda/count($results)) * 100);
    }

    protected function execute(InputInterface $input, OutputInterface $consoleOutput)
    {
        $this->prepare();
        $court = $input->getArgument(static::ARGUMENT_COURT);
        $this->court_id = Court::$types[$court];
        $this->output .= $court."\n";
        $this->processCase();
        $this->finalize(0,$this->output,"Hotovo");
    }
}