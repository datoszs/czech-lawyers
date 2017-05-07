<?php
namespace App\Model\Services;


use App\Model\Jobs\Job;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use DateInterval;
use DatePeriod;
use DateTime;
use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;
use Tracy\Debugger;
use Tracy\ILogger;

class JobService
{

	/** @var Orm */
	private $orm;

	/** @var Connection */
	private $connection;


	public function __construct(Orm $orm, Connection $connection)
	{
		$this->orm = $orm;
		$this->connection = $connection;
	}

	/** @return Job */
	public function getByClassName($className)
	{
		return $this->orm->jobs->getBy([
			'name' => $className
		]);
	}

	public function findAll()
	{
		return $this->orm->jobs->findAll()->fetchAll();
	}

	public function findAllSortedByExecuted()
	{
		return $this->orm->jobs->findAllSortedByExecuted();
	}

	/** @return Job */
	public function get($id)
	{
		return $this->orm->jobs->getById($id);
	}

	/** @return JobRun[] */
	public function findRuns($jobId)
	{
		return $this->orm->jobRuns->findBy(['job' => $jobId])->orderBy('executed', 'DESC')->fetchAll();
	}

	/** @return JobRun */
	public function findRun($runId)
	{
		return $this->orm->jobRuns->getById($runId);
	}

	/** @return JobRun */
	public function newRun(Job $job)
	{
		$entity = new JobRun();
		$entity->job = $job;
		$entity->executed = new DateTime();
		$this->orm->jobRuns->persistAndFlush($entity);
		return $entity;
	}

	public function finishRun(JobRun $jobRun, int $returnCode, ?string $output, ?string $message)
	{
		$jobRun->finished = new DateTime();
		$jobRun->returnCode = $returnCode;
		$jobRun->output = $output;
		$jobRun->message = $message;
		$this->orm->jobRuns->persistAndFlush($jobRun);
	}

	/**
	 * Returns associative array indexed by dates (Y-m-d format) containing number of failed job runs on given date.
	 *
	 * @return array
	 */
	public function getFailedInLastTwoWeeks()
	{
		try {
			$rows = $this->connection->query('
				SELECT (executed::date)::text AS day, COUNT(*) AS count FROM job_run WHERE return_code != 0 AND executed > NOW () - INTERVAL \'10 days\' GROUP BY executed::date
			')->fetchPairs('day', 'count');
			$period = new DatePeriod(
				new DateTime('-10 days'),
				new DateInterval('P1D'),
				new DateTime()
			);
			$output = [];
			/** @var DateTime $day */
			foreach ($period as $day) {
				if (isset($rows[$day->format('Y-m-d')])) {
					$output[$day->format('Y-m-d')] = $rows[$day->format('Y-m-d')];
				} else {
					$output[$day->format('Y-m-d')] = 0;
				}
			}
			return $output;
		} catch (QueryException $exception) {
			Debugger::log($exception, ILogger::EXCEPTION);
			return [];
		}
	}
}
