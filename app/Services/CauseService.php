<?php
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
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

	public function findAll()
	{
		return $this->orm->causes->findAll()->fetchAll();
	}

	public function findFromCourt(Court $court)
	{
		return $this->orm->causes->findBy(['court' => $court])->fetchAll();
	}

	public function findOrCreate(Court $court, $registrySign, JobRun $jobRun = null)
	{
		$entity = $this->orm->causes->getBy(['registrySign' => $registrySign]);
		if (!$entity) {
			$entity = new Cause();
			$entity->court = $court;
			$entity->registrySign = $registrySign;
			$entity->jobRun = $jobRun;
			$this->orm->persist($entity);
		}
		// Update court when needed (must due to potential typo in the import command)
		if ($entity->court != $court) {
			$entity->court = $court;
			$this->orm->persistAndFlush($entity);
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

	public function remove(IEntity $entity)
	{
		$this->orm->remove($entity);
	}

	public function findForTagging(Court $court)
	{
		return $this->orm->causes->findForTagging($court->id);
	}

}