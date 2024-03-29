<?php
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;

class CauseService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function get($causeId)
	{
		return $this->orm->causes->getById($causeId);
	}

	public function getRelevantForAdvocates($causeId): ?Cause
	{
		return $this->orm->causes->getRelevantForAdvocatesById($causeId);
	}

	public function find($registrySign)
	{
		return $this->orm->causes->getBy(['registrySign' => $registrySign]);
	}

	public function search(?string $query, int $start = 0, int $count = 100, string $strategy)
	{
		return $this->orm->causes->search($query, $start, $count, $strategy)->fetchAll();
	}

	public function findAll()
	{
		return $this->orm->causes->findAll()->fetchAll();
	}

	public function detach(Cause $cause): void
	{
		$this->orm->causes->detach($cause);
	}

	/**
	 * Finds causes according to given conditions
	 *
	 * @param int|null $court
	 * @param null|string $registryMark Optional registry mark (to match prefix)
	 * @param string $advocateFilter Filtering of case result tagging status (ok, failed, disputed)
	 * @param string $resultFilter Filtering of advocate tagging status (ok, failed, disputed)
	 * @return Cause[]|ICollection
	 */
	public function findForManualTagging(?int $court, ?string $registryMark, string $advocateFilter, string $resultFilter)
	{
		return $this->orm->causes->findForManualTagging($court, $registryMark, $advocateFilter, $resultFilter);
	}

	public function findFromAdvocate(int $advocateId, ?int $court, ?int $year, ?string $result)
	{
		return $this->orm->causes->findFromAdvocate($advocateId, $court, $year, $result);
	}

	public function findFromCourt(Court $court)
	{
		return $this->orm->causes->findBy(['court' => $court])->fetchAll();
	}

	public function findOrCreate(Court $court, $registrySign, int $year, JobRun $jobRun = null)
	{
		$entity = $this->orm->causes->getBy(['registrySign' => $registrySign]);
		if (!$entity) {
			$entity = new Cause();
			$entity->court = $court;
			$entity->registrySign = $registrySign;
			$entity->jobRun = $jobRun;
			$entity->year = $year;
			$this->orm->persist($entity);
		}
		// Update court when needed (must due to potential typo in the import command)
		if ($entity->court != $court) {
			$entity->court = $court;
			$this->orm->persistAndFlush($entity);
		}
		return $entity;
	}

	public function save(Cause $entity)
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

	public function findForResultTagging(Court $court)
	{
		return $this->orm->causes->findForResultTagging($court->id);
	}

	public function findForSuccessTagging(Court $court)
	{
		return $this->orm->causes->findForSuccessTagging($court->id);
	}

	public function findForAdvocateTagging(Court $court)
	{
		return $this->orm->causes->findForAdvocateTagging($court->id);
	}

}
