<?php
namespace App\Model\Services;

use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Taggings\TaggingCaseResult;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Orm;
use Nextras\Dbal\Connection;
use Nextras\Orm\Entity\IEntity;


class TaggingService
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

	public function insert($result)
	{
		$this->orm->persist($result);
	}

	public function persist(IEntity $entity)
	{
		$this->orm->persist($entity);
	}

	public function flush()
	{
		$this->orm->flush();
	}

	public function findAll()
    {
        return $this
            ->orm
            ->taggingCaseResults
            ->findAll()
            ->fetchAll();
    }

	public function findByDocument(IEntity $document)
	{
		return $this
			->orm
			->taggingCaseResults
			->getBy(['document' => $document]);
	}

	public function findAdvocateTaggingsByCase(Cause $case)
	{
		return $this
			->orm
			->taggingAdvocates
			->findBy(['case' => $case])
			->fetchAll();
	}


	public function findCaseResultLatestTaggingByCases(array $cases)
	{
		$casesIds = array_map(function (Cause $case) {
			return $case->id;
		}, $cases);
		if (count($casesIds) == 0) {
			return [];
		}
		return $this->orm->taggingCaseResults->findLatestTagging($casesIds)->fetchAll();
	}

	public function findLatestTaggingByAdvocate(array $advocate)
	{
		$advocatesIds = array_map(function (Advocate $case) {
			return $case->id;
		}, $advocate);
		if (count($advocatesIds) == 0) {
			return [];
		}
		return $this->orm->taggingAdvocates->findLatestTaggingByAdvocates($advocatesIds)->fetchAll();
	}

	public function getLatestAdvocateTaggingFor(Cause $case)
	{
		return $this->orm->taggingAdvocates->getLatestTagging($case->id)->fetch();
	}

	public function findLatestAdvocateTaggingByCases(array $cases)
	{
		$casesIds = array_map(function (Cause $cause) { return $cause->id; }, $cases);
		return $this->orm->taggingAdvocates->findLatestTagging($casesIds)->fetchAll();
	}

	public function computeAdvocatesStatistics(array $advocatesIds)
	{
		if (count($advocatesIds) === 0) {
			$advocatesIds[] = null;
		}
		$data = $this->connection->query('
		SELECT
			advocate_id,
			case_result,
			COUNT(*) AS count
		FROM "case"
		JOIN vw_latest_tagging_case_result AS last_taggings ON "case".id_case = last_taggings.case_id AND last_taggings.status = %s
		JOIN vw_latest_tagging_advocate AS last_taggings_advocate ON "case".id_case = last_taggings_advocate.case_id AND last_taggings_advocate.status = %s
		WHERE advocate_id IN %?i[]
		GROUP BY advocate_id, case_result
		',
			TaggingStatus::STATUS_PROCESSED,
			TaggingStatus::STATUS_PROCESSED,
			$advocatesIds
		)->fetchAll();
		$output = [];
		foreach ($data as $row) {
			$output[$row->advocate_id][$row->case_result] = $row->count;
		}
		return $output;
	}

	public function computeAdvocateStatisticsPerYear(int $advocateId, ?int $courtId)
	{
		$data = $this->connection->query('
		SELECT
			SUBSTRING(registry_sign FROM \'/(.*)$\') AS year,
			case_result,
			COUNT(*) AS count
		FROM "case"
		JOIN vw_latest_tagging_case_result AS last_taggings ON "case".id_case = last_taggings.case_id AND last_taggings.status = %s 
		JOIN vw_latest_tagging_advocate AS last_taggings_advocate ON "case".id_case = last_taggings_advocate.case_id AND last_taggings_advocate.status = %s
		WHERE advocate_id = %i AND (%?i IS NULL OR court_id = %?i)
		GROUP BY SUBSTRING(registry_sign FROM \'/(.*)$\'), case_result
		',
			TaggingStatus::STATUS_PROCESSED,
			TaggingStatus::STATUS_PROCESSED,
			$advocateId,
			$courtId,
			$courtId
		)->fetchAll();
		$output = [];
		foreach ($data as $row) {
			$output[$row->year][$row->case_result] = $row->count;
		}
		return $output;
	}


	/**
	 * Persist (but not flush) given entity if it is new case result tagging (i.e. when no such previous exists).
	 * @param TaggingCaseResult $entity
	 * @return bool
	 */
	public function persistCaseResultIfDiffers(TaggingCaseResult $entity)
	{
		$old = $this->orm->taggingCaseResults->getLastTagging($entity->case->id)->fetch();
		if ($this->isTaggingDifferent($entity, $old)) {
			$this->persist($entity);
			return true;
		}
		return false;
	}

	public function persistAdvocateIfDiffers(TaggingAdvocate $entity)
	{
		$old = $this->orm->taggingAdvocates->getLatestTagging($entity->case->id)->fetch();
		if ($this->isTaggingAdvocateDifferent($entity, $old)) {
			$this->persist($entity);
			return true;
		}
		return false;
	}

	/**
	 * Returns true/false according to semantic difference of tagging.
	 * @param TaggingCaseResult $new New case tagging
	 * @param TaggingCaseResult|null $old Old case tagging (or null when no such tagging exists)
	 * @return bool
	 */
	private function isTaggingDifferent(TaggingCaseResult $new, $old)
	{
		return $old === null || !($old instanceof TaggingCaseResult) || $new->case != $old->case || $new->document != $old->document || $new->isFinal != $old->isFinal || $new->caseResult != $old->caseResult || $new->status != $old->status || $new->debug != $old->debug;
	}

    /**
     * @param TaggingAdvocate $new
     * @param $old
     * @return bool
     */
    private function isTaggingAdvocateDifferent(TaggingAdvocate $new, $old)
    {
        return $old === null || !($old instanceof TaggingAdvocate) || $new->case != $old->case || $new->document != $old->document || $new->isFinal != $old->isFinal || $new->advocate != $old->advocate || $new->status != $old->status || $new->debug != $old->debug;
    }
}
