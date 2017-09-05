<?php
namespace App\Model\Services;

use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Orm;
use DateTimeImmutable;
use Nette\NotImplementedException;
use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use PDOException;
use Tracy\Debugger;

class AdvocateService
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

	public function search($phrase, int $start = 0, ?int $limit = null)
	{
		return $this->orm->advocates->search($phrase, $start, $limit)->fetchAll();
	}

	/**
	 * Returns advocates with same name (without degrees) now or in history.
	 * Note: takes previous names of given advocates also into account.
	 * Note: ignores the advocate itself
	 * @param Advocate $advocate
	 * @return Advocate[]
	 */
	public function findOfSameName(Advocate $advocate)
	{
		return $this->orm->advocates->findOfSameName($advocate->id)->fetchAll();
	}

	/**
	 * @param string $identificationNumber
	 * @return Advocate|null
	 */
	public function findByIdentificationNumber($identificationNumber): ?Advocate
	{
		return $this->orm->advocates->findBy(['identificationNumber' => $identificationNumber])->fetch();
	}

	/**
	 * @param string $remoteIdentificator
	 * @return Advocate|null
	 */
	public function findbyRemoteIdentificator($remoteIdentificator): ?Advocate
	{
		return $this->orm->advocates->findBy(['remoteIdentificator' => $remoteIdentificator])->fetch();
	}

	/**
	 * Returns advocates from given decile
	 *
	 * @param int $decile
	 * @param int $start
	 * @param int $count
	 * @param bool $reverse
	 * @return Advocate[]|ICollection
	 */
	public function findFromDecile(int $decile, int $start, int $count, bool $reverse)
	{
		return $this->orm->advocates->findFromDecile($decile, $start, $count, $reverse);
	}

	/**
	 * Updates advocate scores
	 *
	 * @return bool True if update was successful, false otherwise
	 */
	public function updateScores()
	{
		try {
			$this->connection->query('REFRESH MATERIALIZED VIEW CONCURRENTLY vm_advocate_score');
			return true;
		} catch (QueryException $exception) {
			Debugger::log($exception);
			return false;
		}
	}

	/**
	 * Returns advocate decile or null
	 *
	 * @param Advocate $advocate
	 * @return int|NULL
	 */
	public function getAdvocateDecile(Advocate $advocate) : ?int
	{
		try {
			$field = $this->connection
				->query('SELECT decile FROM vm_advocate_score WHERE id_advocate = %i', $advocate->id)
				->fetchField();
			if ($field !== null) {
				return (int) $field;
			}
			return null;
		} catch (QueryException $exception) {
			Debugger::log($exception);
			return null;
		}
	}

}
