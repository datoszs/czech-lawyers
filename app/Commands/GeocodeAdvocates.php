<?php
namespace App\Commands;

use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Jobs\JobRun;
use App\Model\Services\AdvocateService;
use App\Model\Services\JobService;
use App\Utils\Validators;
use App\Utils\JobCommand;
use DATOSCZ\MapyCzGeocoder\Exceptions\GeocodingException;
use DATOSCZ\MapyCzGeocoder\Exceptions\MultipleResultsException;
use DATOSCZ\MapyCzGeocoder\Exceptions\NoResultException;
use DATOSCZ\MapyCzGeocoder\IGeocoder;
use DATOSCZ\MapyCzGeocoder\Providers\MapyCZ;
use Nette\InvalidStateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;
use Tracy\ILogger;

class GeocodeAdvocates extends Command
{
	use JobCommand;
	use Validators;

	const NEW = 'new';
	const CHANGED = 'changed';
	const FAILED = 'failed';
	const NO_ADDRESS = 'address';
	const NO_RESULTS = 'no_results';
	const MULTIPLE_RESULTS = 'multiple_results';
	const NO_CHANGE = 'no_change';

	const ALL = 'all';
	const ALL_SHORTCUT = "a";

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_ERROR = 1;

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var JobService @inject */
	public $jobService;

	/** @var IGeocoder */
	private $geocoder;

	protected function configure()
	{
		$this->setName('app:geocode-advocates')
			->setDescription('Updates advocates coordinates based on their address.')
			->addOption(
				static::ALL,
				static::ALL_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should all addresses be geocoded again?'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$all = $input->getOption(static::ALL);
		$code = 0;
		$output = null;
		$message = null;
		if ($all) {
			$advocates = $this->advocateService->findAll();
		} else {
			$job = $this->jobService->getByClassName(static::class);
			/** @var JobRun $lastRun */
			$lastRun = $job->runs->countStored() > 0 ? $job->runs->get()->fetch() : null;
			if ($lastRun) {
				$advocates = $this->advocateService->findWithChangedInfos($lastRun->executed);
			} else {
				$advocates = $this->advocateService->findAll();
			}
		}
		$this->geocoder = new MapyCZ();

		$new = 0;
		$changed = 0;
		$noChange = 0;
		$failed = 0;
		$noAddress = 0;
		$noResults = 0;
		$multipleResults = 0;
		/** @var Advocate $advocate */
		foreach ($advocates as $advocate) {
			$state = $this->processAdvocate($consoleOutput, $output, $advocate);
			if ($state === self::NEW) {
				$new++;
			} elseif($state === self::CHANGED) {
				$changed++;
			} elseif($state === self::FAILED) {
				$failed++;
			} elseif($state === self::NO_ADDRESS) {
				$noAddress++;
			} elseif($state === self::NO_RESULTS) {
				$noResults++;
			} elseif($state === self::MULTIPLE_RESULTS) {
				$multipleResults++;
			} elseif($state === self::NO_CHANGE) {
				$noChange++;
			} else {
				throw new InvalidStateException("Invalid advocate geocoding state [{$state}].");
			}
		}

		$this->advocateService->flush();

		$message = "Statistics: \nNot changed: {$noChange}\nNew: {$new}\nChanged: {$changed}\nNo address: {$noAddress}\nNo results: {$noResults}\nMultiple results: {$multipleResults}\nFailed: {$failed}\n";
		$output .= $message;
		$consoleOutput->write($message);

		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}

	public function processAdvocate(OutputInterface $consoleOutput, &$output, Advocate $advocate): string
	{
		/** @var AdvocateInfo $advocateInfo */
		$advocateInfo = $advocate->getCurrentAdvocateInfo();
		if (!$advocateInfo) {
			$temp = sprintf("Advocate %s: no address info", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			return self::NO_ADDRESS;
		}
		if (!$advocateInfo->street || !$advocateInfo->city || !$advocateInfo->postalArea) {
			$temp = sprintf("Advocate %s: no address", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			return self::NO_ADDRESS;
		}
		$address = implode(' ', [
			$advocateInfo->street,
			$advocateInfo->city,
			$advocateInfo->postalArea
		]);
		try {
			$coordinates = $this->geocoder->geocode($address);
		} catch (GeocodingException $e) {
			$temp = sprintf("Advocate %s geocoded: failed.", $advocate->getCurrentName());
			Debugger::log($e, ILogger::EXCEPTION);
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			return self::FAILED;
		} catch (MultipleResultsException $e) {
			$temp = sprintf("Advocate %s geocoded: multiple results. ", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			return self::MULTIPLE_RESULTS;
		} catch (NoResultException $e) {
			$temp = sprintf("Advocate %s geocoded: no results.", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			return self::NO_RESULTS;
		}
		$oldLocation = $advocateInfo->location;
		if ($oldLocation) {
			if ($oldLocation->getLatitude() === $coordinates->getLatitude() && $coordinates->getLongitude() === $oldLocation->getLongitude()) {
				$temp = sprintf("Advocate %s geocoded: no change.", $advocate->getCurrentName());
				$output .= $temp . "\n";
				$consoleOutput->writeln($temp);
				return self::NO_CHANGE;
			}
			$temp = sprintf("Advocate %s geocoded: changed.", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			$advocateInfo->location = $coordinates;
			$this->advocateService->persist($advocateInfo);
			return self::CHANGED;
		} else {
			$temp = sprintf("Advocate %s geocoded: new.", $advocate->getCurrentName());
			$output .= $temp . "\n";
			$consoleOutput->writeln($temp);
			$advocateInfo->location = $coordinates;
			$this->advocateService->persist($advocateInfo);
			return self::NEW;
		}
	}

}
