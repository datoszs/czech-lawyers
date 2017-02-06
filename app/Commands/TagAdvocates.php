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
use Nette\Utils\DateTime;
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
    protected $shoda = 0;
    protected $ignored = 0;
    protected $failed = 0;
    protected $bad = 0;
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

    protected function prepareAndSave(Advocate $advocate, Cause $case, $debug)
    {
        $tagAdvocate = new TaggingAdvocate();
        $tagAdvocate->advocate = $advocate;
        $tagAdvocate->case = $case;
        $tagAdvocate->status = TaggingStatus::STATUS_PROCESSED;
        $tagAdvocate->isFinal = false;
        $tagAdvocate->document = null;
        $tagAdvocate->debug = $debug;
        $tagAdvocate->insertedBy = $this->user;
        $tagAdvocate->jobRun = $this->jobRun;

        $result = $this->taggingService->persistAdvocateIfDiffers($tagAdvocate);
        if ($result) {
            $this->output .= sprintf("Tagging advocate result for case [%s] of [%s]\n", $case->registrySign, $case->court->name);
        }
    }

    protected function processCase()
    {
        $court_id = $this->court_id;
        print($court_id . "\n");
        /* @var Cause[] $results */
        $results = $this->orm->causes->findTaggingResultsByCourt($court_id)->fetchAll();
        $advocates = $this->orm->advocatesInfo->findUniqueNames()->fetchAll();
        $couses = count($results);

        print("Nalezeno ohodnocenych kauz: " . $couses . "\n");
        print("Nalezeno unikatnich jmen: " . count($advocates) . "\n");
        $start = new DateTime('now');
        print($start->format('Y-m-d H:i:s') . "\n");
        /* @var AdvocateInfo $advocate */
        /* @var Cause $result */
        $indexToRemove = [];
        foreach ($advocates as $advocate) {
            foreach ($results as $index => $result) {
                $raw_data = $result->officialData;

                if (is_array($raw_data) && count($raw_data) == 1) {
                    $name = null;
                    $surname = null;
                    $data_j = JSON::decode(JSON::encode(array_values($raw_data)), true)[0];

                    if ($court_id == Court::TYPE_US) {
                        $name = Strings::lower($data_j["name"]);
                        $surname = Strings::lower($data_j["surname"]);
                        $debug = $name . " " . $surname;
                        if ($name === "" or $surname === "") {
                            array_push($indexToRemove, $index);
                            printf("neúplné: %s, %s\n", $name, $surname);
                            $this->empty++;
                            continue;
                        }

                    } elseif ($court_id == Court::TYPE_NSS) {
                        $name = Strings::lower($data_j["names"]);
                        $debug = $name;
                    } else {
                        continue;
                    }
                } else {
                    array_push($indexToRemove, $index);
                    //printf("odstraňuji: %s\n", $result->registrySign);
                    $this->bad++;
                    continue;
                }

                if (Strings::lower($advocate->name) === $name &&
                    Strings::lower($advocate->surname) === $surname
                ) {
                    print($result->registrySign . "\n");
                    printf("%s %s, %d, %s z %d\n", $name, $surname, $advocate->advocate->id, end($advocate->email), count($results));
                    $this->prepareAndSave($advocate->advocate, $result, $debug);

                    array_push($indexToRemove, $index);
                    $this->shoda++;
                    continue;
                } elseif (Strings::contains($name, Strings::lower($advocate->name . " " . $advocate->surname))) {

                    print($result->registrySign . "\n");
                    printf("%s, %d, '%s', %s z %d\n", $advocate->name . " " . $advocate->surname, $advocate->advocate->id, $name, end($advocate->email), count($results));

                    $this->prepareAndSave($advocate->advocate, $result, $debug);
                    array_push($indexToRemove, $index);
                    $this->shoda++;
                    continue;
                }


            }
            if (count($indexToRemove) > 100) {
                printf("----> Items to delete: %d\n", count($indexToRemove));
                while (($item = array_pop($indexToRemove)) != null) {
                    array_splice($results, $item, 1);
                }
                $results = array_values($results);
            }
        }
        $this->taggingService->flush();
        print("Nalezeno ohodnocenych kauz: " . $couses . "\n");
        print("Nalezeno unikatnich jmen: " . count($advocates) . "\n");
        $message = sprintf("\nNalezeno: %d shod; neúplných: %d; nevhodných: %s, úspěšnost: %f%%",
            $this->shoda,
            $this->empty,
            $this->bad,
            ($this->shoda / ($couses-$this->empty-$this->bad) * 100));
        $this->output .= $message;
        $end = new DateTime('now');
        printf("%s\n%s\n", $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $interval = $end->diff($start);
        printf("%s %s:%s:%s\n", $interval->d, $interval->h, $interval->i, $interval->s);
        print($message);
    }

    protected
    function execute(InputInterface $input, OutputInterface $consoleOutput)
    {
        $this->prepare();
        $court = $input->getArgument(static::ARGUMENT_COURT);
        $this->court_id = Court::$types[$court];
        $this->output .= $court . "\n";
        $this->processCase();
        $this->finalize(0, $this->output, "Hotovo");
    }
}