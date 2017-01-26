<?php
namespace App\Model\Services;

use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Orm;
use DateTimeImmutable;
use Nette\NotImplementedException;
use Nextras\Orm\Entity\IEntity;

class AdvocateService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function get($advocateId)
	{
		return $this->orm->advocates->getById($advocateId);
	}

	public function insert(Advocate $advocate, AdvocateInfo $advocateInfo = null, $flush = false)
	{
		$this->orm->persist($advocate);
		if ($advocateInfo) {
			$this->orm->persist($advocateInfo);
		}
		if ($flush) {
			$this->orm->flush();
		}
	}

	public function flush()
	{
		$this->orm->flush();
	}

	public function persist(IEntity $entity)
	{
		$this->orm->persist($entity);
	}

	public function invalidateOldInfos(Advocate $entity, AdvocateInfo $except)
	{
		foreach ($entity->advocateInfo as $info) {
			if ($info === $except) {
				continue;
			}
			$info->validTo = new DateTimeImmutable();
		}
	}

	public function search($phrase, $limit = null)
	{
		return $this->orm->advocates->search($phrase, $limit)->fetchAll();
	}

	/**
	 * @param string $identificationNumber
	 * @return Advocate|null
	 */
	public function findByIdentificationNumber($identificationNumber)
	{
		return $this->orm->advocates->findBy(['identificationNumber' => $identificationNumber])->fetch();
	}

}