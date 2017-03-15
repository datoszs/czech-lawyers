<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 06.01.2017
 * Time: 20:41
 */

namespace App\Commands;


use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Services\AdvocateService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nextras\Dbal\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class USAdvocateTagger extends AdvocateTagger
{
	protected $court_id;
	protected $shoda = 0;
	protected $bad = 0;
	protected $output = "";
	protected $start;
	protected $advocates = null;
	protected $noMatch = null;


	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var Connection @inject */
	public $connection;

	protected function configure()
	{
		$this->setName('app:us-advocate-tagger')
			->setDescription('Tag advocates for NSS and ÚS.');
		/*->addArgument(
            static::ARGUMENT_COURT,
            InputArgument::REQUIRED,
            'Identificator of court');*/
	}

	protected function beforeExecute()
	{
		$this->court = $this->courtService->getUS();
		$this->advocates = $this->prepareAdvocates();
		print("Nalezeno unikatnich jmen: " . count($this->advocates) . "\n");
		$this->start = new DateTime('now');
		print($this->start->format('Y-m-d H:i:s') . "\n");
		$this->noMatch = [];
	}

	protected function prepareAdvocates()
	{
		return $this->connection
			->query('
			SELECT concat_ws(\' \',  name, surname) AS fullname, string_agg(DISTINCT advocate_id::text, \' \') AS advocate_id
			FROM advocate_info
			GROUP BY name, surname
			HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1')
			->fetchPairs('fullname', 'advocate_id');
	}

	protected function getCleanName($data)
	{
		return preg_replace('/!\s+!/', ' ', $data);
	}

	protected function match($data, $name)
	{
		$status = TaggingStatus::STATUS_FAILED;
		$match = false;
		if (strpos($name, $data) !== false) {
			$status = TaggingStatus::STATUS_PROCESSED;
			$match = true;
		}
		return [$match, $status];
	}

	protected function prepareTagging(Cause $case, $debug, $status, Advocate $advocate = null)
	{
		$tagAdvocate = new TaggingAdvocate();
		$tagAdvocate->advocate = $advocate;
		$tagAdvocate->case = $case;
		$tagAdvocate->status = $status;
		$tagAdvocate->isFinal = false;
		$tagAdvocate->document = null;
		$tagAdvocate->debug = $debug;
		$tagAdvocate->insertedBy = $this->user;
		$tagAdvocate->jobRun = $this->jobRun;

		return $tagAdvocate;
	}

	protected function processCase(Cause $cause, string &$output, OutputInterface $consoleOutput)
	{
		if (!$cause->officialData) {
			return false;
		}
		//$consoleOutput->writeln(count($cause->officialData));
		if (count($cause->officialData) > 1) {
			return false;
		}
		$name = array_unique(array_column($cause->officialData, "name"))[0];
		$surname = array_unique(array_column($cause->officialData, "surname"))[0];
		$fullName = $this->getCleanName($name." ".$surname);
		$consoleOutput->writeln("text: " . $fullName);
		foreach ($this->advocates as $advocateName => $advocateId) {
			list($matched, $status) = $this->match($fullName, $advocateName);
			if ($matched) {
				//$consoleOutput->writeln($cause->registrySign);
				$this->shoda++;
				if (array_search($advocateName, array_keys($this->advocates)) > 10) {
					unset($this->advocates[$advocateName]);
					$this->advocates = [$advocateName => $advocateId] + $this->advocates;
				}
				$consoleOutput->writeln(sprintf("\t=> %d, %s (%d, %d)", $advocateId, $advocateName, $this->shoda, $this->bad));
				$tagAdvocate = $this->prepareTagging($cause, $fullName, $status, $this->advocateService->get($advocateId));
				return $this->taggingService->persistAdvocateIfDiffers($tagAdvocate);
			} else
				continue;
		}
		$tagAdvocate = $this->prepareTagging($cause, "No match: " . $fullName, TaggingStatus::STATUS_FAILED);
		$consoleOutput->writeln("\t=> bez shody");
		$this->noMatch[$fullName] = 1;
		return $this->taggingService->persistAdvocateIfDiffers($tagAdvocate);
	}
}
