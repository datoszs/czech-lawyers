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

class NSSAdvocateTagger extends AdvocateTagger
{
	protected $court_id;
	protected $shoda = 0;
	protected $bad = 0;
	protected $output = "";
	protected $start;
	protected $advocates = null;
	protected $noMatch = null;
	protected $skip_word = ["sama", "§", "s.r.o.", "a.s.", "o.s.", "v.o.s.", "spol.", "kancelář"];


	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var Connection @inject */
	public $connection;

	protected function configure()
	{
		$this->setName('app:nss-advocate-tagger')
			->setDescription('Tag advocates for NSS and ÚS.');
		/*->addArgument(
            static::ARGUMENT_COURT,
            InputArgument::REQUIRED,
            'Identificator of court');*/
	}

	protected function beforeExecute()
	{
		$this->court = $this->courtService->getNSS();
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
			SELECT concat_ws(\' \', degree_before, concat(\'/\', name), concat(surname, \'/\')) AS fullname, string_agg(DISTINCT advocate_id::text, \' \') AS advocate_id
			FROM advocate_info
			GROUP BY degree_before, name, surname
			HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1')
			->fetchPairs('fullname', 'advocate_id');
	}

	protected function containsSkipWord($text)
	{
		foreach ($this->skip_word as $word) {
			if (Strings::contains($text, $word))
				return true;
		}
		return false;
	}

	protected function clearData($text)
	{
		/* remove multiple whitespaces and all after last - */
		return preg_replace('/\s?-\s?.+$/', '', preg_replace('/!\s+!/', ' ', $text));

	}

	protected function getCleanName($data)
	{
		if ($this->containsSkipWord($data))
			return null;
		$cleanData = $this->clearData($data);

		/* more names in text */
		preg_match_all('/(\s?\b\p{Lu}\p{Ll}+(?!\.)\b){2,3}/u', $cleanData, $matches);
		if ($matches && count($matches[0]) > 1) {
			return null;
		}
		return $cleanData;
	}

	protected function checkText($text)
	{
		if ($text === null) {
			$this->bad++;
			return false;
		}
		if (isset($this->noMatch[$text])) {
			return false;
		}
		return true;
	}

	protected function checkEquivalence($data, $findName)
	{
		preg_match('/(\s?\b\p{Lu}\p{Ll}+(?!\.)\b){2,3}/u', $data, $matches);
		if ($matches) {
			return Strings::length(trim($matches[0])) == Strings::length($findName);
		}
		return false;
	}

	protected function prepareNames($advocateName)
	{
		$advocateName = preg_replace('/!\s+!/', ' ', $advocateName);
		preg_match('/\/(.+)\//', $advocateName, $matches);

		$name = explode(" ", $matches[1]);
		$reverseName = $name;
		if (count($name) == 2) {
			$reverseName = array_reverse($name);
		}

		return array(
			"normal" => implode(" ", $name),
			"reverse" => implode(" ", $reverseName),
			"full" => str_replace('/', '', $advocateName));

	}

	protected function match($data, $names)
	{
		$status = TaggingStatus::STATUS_FAILED;
		$match = false;
		if (stripos($data, $names["full"]) !== false) {
			//print("\tfull compare\n");
			$status = TaggingStatus::STATUS_PROCESSED;
			$match = true;
		} /*elseif (preg_match('/\b'.$names["normal"].'\b/', $data)) {
			$status = TaggingStatus::STATUS_FUZZY;
			$match = true;
		}elseif (preg_match('/\b'.$names["reverse"].'\b/', $data)) {
			$status = TaggingStatus::STATUS_FUZZY;
			$match = true;
		}
		*/
		elseif (strripos($data, $names["normal"]) !== false) {
			if ($this->checkEquivalence($data, $names["normal"])) {
				//print("\tnormal compare\n");
				$status = TaggingStatus::STATUS_PROCESSED;
				$match = true;
			}

		} elseif (strripos($data, $names["reverse"]) !== false) {
			if ($this->checkEquivalence($data, $names["reverse"])) {
				//print("\treverse compare\n");
				$status = TaggingStatus::STATUS_PROCESSED;
				$match = true;
			}
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
		$rawData = array_unique(array_column($cause->officialData, "names"))[0];
		$text = $this->getCleanName($rawData);
		if (!$this->checkText($text)) {
			/* save empty record */
			return false;
		}
		//$consoleOutput->writeln("text: " . $rawData . " -> " . $text);
		foreach ($this->advocates as $advocateName => $advocateId) {
			$nameVariant = $this->prepareNames($advocateName);
			list($matched, $status) = $this->match($text, $nameVariant);
			if ($matched) {
				//$consoleOutput->writeln($cause->registrySign);
				$this->shoda++;
				if (array_search($advocateName, array_keys($this->advocates)) > 10) {
					unset($this->advocates[$advocateName]);
					$this->advocates = [$advocateName => $advocateId] + $this->advocates;
				}
				//$consoleOutput->writeln(sprintf("\t=> %d, %s (%d, %d)", $advocateId, $nameVariant["full"], $this->shoda, $this->bad));
				$tagAdvocate = $this->prepareTagging($cause, $rawData, $status, $this->advocateService->get($advocateId));
				return $this->taggingService->persistAdvocateIfDiffers($tagAdvocate);
			} else
				continue;
		}
		$tagAdvocate = $this->prepareTagging($cause, "No match: " . $rawData, TaggingStatus::STATUS_FAILED);
		//$consoleOutput->writeln("\t=> bez shody");
		$this->noMatch[$text] = 1;
		return $this->taggingService->persistAdvocateIfDiffers($tagAdvocate);
	}
}
