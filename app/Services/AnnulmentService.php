<?php declare(strict_types=1);
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Annulments\Annulment;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Entity\IEntity;


class AnnulmentService
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

	public function flush()
	{
		$this->orm->flush();
	}

	/**
	 * Returns annulment with given ID or null
	 * @param int $id
	 * @return Annulment|null
	 */
	public function get($id): Annulment
	{
		return $this->orm->annulments->getById($id);
	}

	public function getPair(Cause $annuledCase, ?Cause $annulingCase): ?Annulment
	{
		$entity = $this->orm->annulments->getBy([
			'annuledCase' => $annuledCase,
			'annulingCase' => $annulingCase]
		);

		return $entity;
	}

	/**
	 * Returns which cases from given are annuled as well as by what case they were annuled
	 * @param array $cases
	 * @return array
	 * @throws QueryException
	 */
	public function findComputedAnnulmentOfCases(array $cases): array
	{
		$casesIds = array_map(function (Cause $case) {
			return $case->id;
		}, $cases);
		if (count($casesIds) == 0) {
			return [];
		}
		$rows = $this->connection->query('SELECT annuled_case, annuling_case FROM vw_computed_case_annulment WHERE annuled_case IN %?i[]', $casesIds)->fetchAll();

		$output = [];
		foreach ($rows as $row) {
			$output[$row->annuled_case][] = $row->annuling_case;
		}
		return $output;
	}

	/**
	 * Returns all cases which are annuled by given cases (in associative array)
	 * @param array $cases
	 * @return array
	 * @throws QueryException
	 */
	public function findComputedAnnulingOfCases(array $cases): array
	{
		$casesIds = array_map(function (Cause $case) {
			return $case->id;
		}, $cases);
		if (count($casesIds) == 0) {
			return [];
		}
		$rows = $this->connection->query('SELECT annuled_case, annuling_case FROM vw_computed_case_annulment WHERE annuling_case IN %?i[]', $casesIds)->fetchAll();
		$output = [];
		foreach ($rows as $row) {
			$output[$row->annuling_case][] = $row->annuled_case;
		}
		return $output;
	}

	public function createAnnulment(Cause $annuledCase, $annulingCase, JobRun $jobRun = null): Annulment
	{
		$entity = new Annulment();
		$entity->annuledCase = $annuledCase;
		$entity->annulingCase = $annulingCase;
		$entity->jobRun = $jobRun;
		$this->orm->persist($entity);

		return $entity;
	}

	public function save(IEntity $entity): void
	{
		$this->orm->persist($entity);
	}

}
