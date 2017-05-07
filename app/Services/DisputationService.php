<?php
namespace App\Model\Services;

use App\Exceptions\AlreadyValidatedDisputeRequestException;
use App\Exceptions\ExpiredDisputeRequestException;
use App\Exceptions\NoSuchDisputeException;
use App\Exceptions\NoSuchDisputeRequestException;
use App\Model\Cause\Cause;
use App\Model\Disputes\Dispute;
use App\Model\Orm;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use DateTimeImmutable;
use Nette\Utils\Random;
use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Collection\ICollection;
use Tracy\Debugger;
use Tracy\ILogger;


class DisputationService
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
	 * Returns dispute with given ID or null
	 * @param int $id
	 * @return Dispute|null
	 */
	public function get($id)
	{
		return $this->orm->disputes->getById($id);
	}

	/**
	 * Returns all validated disputes of given case
	 * @param Cause $cause
	 * @return ICollection
	 */
	public function findByCase(Cause $cause)
	{
		return $this
			->orm
			->disputes
			->findBy([
				'case' => $cause,
				'validatedAt!=' => NULL
			])
			->orderBy('reason')
			->orderBy('inserted', 'ASC');
	}

	/**
	 * Resolves given dispute (of given ID) with given response.
	 * Note: persist is included, flush is not.
	 * @param int $disputeId
	 * @param string $response
	 * @param int $userId
	 * @return Dispute
	 * @throws NoSuchDisputeException
	 */
	public function resolve($disputeId, $response, $userId)
	{
		$dispute = $this->get($disputeId);
		if (!$dispute) {
			throw new NoSuchDisputeException();
		}
		$dispute->response = $response;
		$dispute->resolved = new DateTimeImmutable();
		$dispute->resolvedBy = $this->orm->users->getById($userId);
		$this->orm->persist($dispute);
		return $dispute;
	}

	/**
	 * Create new case disputation request with given parameters
	 * @param Cause $case
	 * @param string $fullname
	 * @param string $from
	 * @param string $content
	 * @param TaggingAdvocate|null $advocateTagging
	 * @param TaggingCaseResult|null $caseResultTagging
	 * @return Dispute
	 */
	public function dispute(Cause $case, string $fullname, string $from, string $content, ?TaggingAdvocate $advocateTagging, ?TaggingCaseResult $caseResultTagging)
	{
		$code = Random::generate(128);
		$entity = new Dispute();
		$entity->fullname = $fullname;
		$entity->case = $case;
		$entity->taggingAdvocate = $advocateTagging;
		$entity->taggingCaseResult = $caseResultTagging;
		$entity->email = $from;
		$entity->reason = $content;
		$entity->validUntil = (new DateTimeImmutable())->modify('+24 hours');
		$entity->code = $code;
		$this->orm->persistAndFlush($entity);
		return $entity;
	}

	/**
	 * Fetch case disputation request if it exists
	 * @param string $email
	 * @param string $code
	 * @return Dispute
	 * @throws AlreadyValidatedDisputeRequestException when this request was already validated
	 * @throws ExpiredDisputeRequestException when request is expired
	 * @throws NoSuchDisputeRequestException when no reguest for such email and validation code
	 */
	public function getDispute(string $email, string $code)
	{
		/** @var Dispute $entity */
		$entity = $this->orm->disputes->getBy([
			'code' => $code,
			'email' => $email,
		]);
		if (!$entity) {
			throw new NoSuchDisputeRequestException();
		}
		if ($entity->validUntil < new DateTimeImmutable()) {
			throw new ExpiredDisputeRequestException();
		}
		if ($entity->validatedAt) {
			throw new AlreadyValidatedDisputeRequestException();
		}
		return $entity;
	}


	/**
	 * Confirms case disputation request if it exists
	 * @param string $email
	 * @param string $code
	 * @throws ExpiredDisputeRequestException when request is expired
	 * @throws NoSuchDisputeRequestException when no reguest for such email and validation code
	 * @throws AlreadyValidatedDisputeRequestException when the request was already validated
	 */
	public function confirmDispute(string $email, string $code)
	{
		/** @var Dispute $entity */
		$entity = $this->orm->disputes->getBy([
			'code' => $code,
			'email' => $email,
		]);
		if (!$entity) {
			throw new NoSuchDisputeRequestException();
		}
		if ($entity->validUntil < new DateTimeImmutable()) {
			throw new ExpiredDisputeRequestException();
		}
		if ($entity->validatedAt) {
			throw new AlreadyValidatedDisputeRequestException();
		}
		$entity->validatedAt = new DateTimeImmutable();
		$this->orm->persistAndFlush($entity);
	}

	/**
	 * Find number of validated but not resolved disputation counts of given causes
	 *
	 * @param array $causesId
	 * @return array
	 * @throws QueryException
	 */
	public function findDisputationCounts(array $causesId)
	{
		if (count($causesId) === 0) {
			$causesId[] = null;
		}
		$data = $this->connection->query('
			SELECT
				case_id,
				COUNT(*) AS count
			FROM case_disputation
			WHERE case_id IN %?i[] AND validated_at IS NOT NULL AND resolved IS NULL
			GROUP BY case_id
			',
			$causesId
		)->fetchAll();
		$output = [];
		foreach ($data as $row) {
			$output[$row->case_id] = $row->count;
		}
		return $output;
	}

	/**
	 * Returns statistics about case disputations (total of valid, number of unresolved and number of pending)
	 * @return array
	 */
	public function getStatistics()
	{
		try {
			$row = $this->connection->query('
				SELECT
					(SELECT COUNT(*) FROM case_disputation WHERE validated_at IS NOT NULL) AS total_valid,
					(SELECT COUNT(*) FROM case_disputation WHERE validated_at IS NOT NULL AND resolved IS NULL) AS unresolved,
					(SELECT COUNT(*) FROM case_disputation WHERE validated_at IS NULL AND valid_until > NOW()) AS pending
			')->fetch();
			if ($row) {
				return $row->toArray();
			}
			return [];
		} catch (QueryException $exception) {
			Debugger::log($exception, ILogger::EXCEPTION);
			return [];
		}
	}
}
