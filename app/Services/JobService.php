<?php
namespace App\Model\Services;


use App\Model\Jobs\Job;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use DateTime;

class JobService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
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

	public function finishRun(JobRun $jobRun, $returnCode, $output, $message)
	{
		$jobRun->finished = new DateTime();
		$jobRun->returnCode = $returnCode;
		$jobRun->output = $output;
		$jobRun->message = $message;
		$this->orm->jobRuns->persistAndFlush($jobRun);
	}
}