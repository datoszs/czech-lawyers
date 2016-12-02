<?php
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Jobs\JobRun;
use App\Model\Orm;

class CauseService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function findOrCreate($registrySign, JobRun $jobRun = null)
	{
		$entity = $this->orm->causes->getBy(['registrySign' => $registrySign]);
		if (!$entity) {
			$entity = new Cause();
			$entity->registrySign = $registrySign;
			$entity->jobRun = $jobRun;
			$this->orm->persist($entity);
		}
		return $entity;
	}

	public function findAll()
    {
        return $this->orm->causes->findAll()->fetchAll();
    }

}