<?php
namespace App\Model\Services;

use App\Exceptions\AlreadyValidatedDisputeRequestException;
use App\Model\Cause\Cause;
use App\Model\Annulments\Annulment;
use App\Model\Jobs\JobRun;
use App\Model\Orm;
use DateTimeImmutable;
use Nette\Utils\Random;
use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Tracy\Debugger;
use Tracy\ILogger;


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
	public function get($id)
	{
		return $this->orm->annulments->getById($id);
	}

	public function findPair(Cause $annuledCase, Cause $annulingCase)
	{
		$entity = $this->orm->annulments->getBy([
			'annuled_case' => $annuledCase,
			'annuling_case' => $annulingCase]
		);

		return $entity;
	}

	public function findByCaseId($caseId) {
		$entity = $this->orm->annulments->getBy(['annuled_case' => $caseId]);
		return $entity;
	}

	public function createAnnulment(Cause $annuledCase, Cause $annulingCase, JobRun $jobRun = null) {

		$entity = new Annulment();
		$entity->annuled_case = $annuledCase;
		$entity->annuling_case = $annulingCase;
		$entity->jobRun = $jobRun;
		$this->orm->persist($entity);

		return $entity;
	}

	public function save(IEntity $entity)
	{
		$this->orm->persist($entity);
	}

}
