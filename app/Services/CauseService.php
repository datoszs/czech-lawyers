<?php
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use Nextras\Orm\Entity\IEntity;

class CauseService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function find($registrySign)
	{
		return $this->orm->causes->getBy(['registrySign' => $registrySign]);
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

	public function save(IEntity $entity)
	{
		$this->orm->persist($entity);
	}

	public function flush()
	{
		$this->orm->flush();
	}

}