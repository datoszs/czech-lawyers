<?php
namespace App\Model\Cause;

use App\Enums\TaggingStatus;
use Mikulas\OrmExt\MappingFactory;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Mapper\Mapper;

class CausesMapper extends Mapper
{
	const FILTER_ANY = 'any';
	const FILTER_OK = 'ok';
	const FILTER_FAILED = 'failed';
	const FILTER_DISPUTED = 'disputed';


	protected function createStorageReflection()
	{
		$factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
		$factory->addJsonMapping('officialData');

		return $factory->getStorageReflection();
	}

	public function getTableName()
	{
		return 'case';
	}

	/**
	 * Return cause but only when it is relevant for advocates portal
	 * Note: filters cases only to allowed ones for advocate portal
	 *
	 * @param int $causeId
	 * @return IEntity
	 */
	public function getRelevantForAdvocatesById(int $causeId)
	{
		$builder = $this->builder();
		$builder->innerJoin('case', 'vw_case_for_advocates', 'vw_case_for_advocates', '"case".id_case = vw_case_for_advocates.id_case');
		$builder->where('"case".id_case = %i', $causeId);
		$builder->limitBy(1);
		$collection = $this->toCollection($builder);
		return $collection->fetch();
	}

	/**
	 * Returns all cases which can be result tagged (the cases with is_final flag set to true are ignored).
	 *
	 * @param int $courtId
	 * @return QueryBuilder
	 */
	public function findForResultTagging($courtId)
	{
		return $this->builder()->where('court_id = %i AND id_case NOT IN (SELECT case_id FROM tagging_case_result WHERE is_final)', $courtId);
	}

    public function findTaggingResultsByCourt($courtId)
    {
        return $this->builder()->where('court_id = %i AND id_case IN (SELECT distinct(case_id) FROM tagging_case_result WHERE is_final=FALSE) AND official_data IS NOT NULL',$courtId)->orderBy('id_case');
    }

	/**
	 * Returns all cases which can be advocate tagged (the cases with is_final flag set to true are ignored).
	 *
	 * @param int $courtId
	 * @return QueryBuilder
	 */
	public function findForAdvocateTagging($courtId)
	{
		return $this->builder()->where('court_id = %i AND id_case NOT IN (SELECT case_id FROM tagging_advocate WHERE is_final)', $courtId);
	}

	/**
	 * Search and return all cases for given phrase.
	 * Note: case insensitive search is used.
	 * Note: filters cases only to allowed ones for advocate portal
	 *
	 * @param string $phrase Phrase to be searched
	 * @param int $start Offset of results
	 * @param int $count Number of results
	 * @param string $strategy Matching strategy. middle: %he%, start: he%, end: %he
	 * @return QueryBuilder
	 */
	public function search(?string $phrase, int $start, int $count, string $strategy)
	{
		if ($strategy === 'start') {
			$matchStrategy = '%like_';
		} elseif ($strategy === 'end') {
			$matchStrategy = '%_like';
		} else {
			$matchStrategy = '%_like_';
		}
		$builder = $this->builder()
			->innerJoin('case', 'vw_case_for_advocates', 'vw_case_for_advocates', '"case".id_case = vw_case_for_advocates.id_case')
			->where('unaccent("case".registry_sign) ILIKE unaccent(' . $matchStrategy . ')', $phrase);
		$builder->limitBy($count, $start);
		return $builder;
	}

	public function findForManualTagging(?int $court, ?string $registryMark, string $advocateFilter, string $resultFilter)
	{
		$builder = $this->builder();
		$builder->orderBy('id_case');
		if ($court) {
			$builder->andWhere('court_id = %i', $court);
		}
		if ($registryMark) {
			$builder->andWhere('registry_sign ILIKE %like_', $registryMark);
		}
		if ($advocateFilter === static::FILTER_FAILED) {
			$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_advocate.status IS NULL OR (vw_latest_tagging_advocate.status NOT IN (%s) AND NOT vw_latest_tagging_advocate.is_final)', TaggingStatus::STATUS_PROCESSED);
		} elseif ($advocateFilter === static::FILTER_DISPUTED) {
			$builder->innerJoin('case', 'case_disputation', 'case_disputation', 'case_disputation.case_id = id_case AND tagging_advocate_disputed AND validated_at IS NOT NULL AND resolved IS NULL');
		} elseif ($advocateFilter === static::FILTER_OK) {
			$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_advocate.status IN (%s) OR vw_latest_tagging_advocate.is_final', TaggingStatus::STATUS_PROCESSED);
		}
		if ($resultFilter === static::FILTER_FAILED) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_case_result.status IS NULL OR (vw_latest_tagging_case_result.status NOT IN (%s) AND NOT vw_latest_tagging_case_result.is_final)', TaggingStatus::STATUS_PROCESSED);
		} elseif ($resultFilter === static::FILTER_DISPUTED) {
			$builder->innerJoin('case', 'case_disputation', 'case_disputation', 'case_disputation.case_id = id_case AND tagging_case_result_disputed AND validated_at IS NOT NULL AND resolved IS NULL');
		} elseif ($advocateFilter === static::FILTER_OK) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_case_result.status IN (%s) OR vw_latest_tagging_case_result.is_final', TaggingStatus::STATUS_PROCESSED);
		}
		return $builder;
	}

	/**
	 * Returns all cases of given advocate
	 * Note: filters cases only to allowed ones for advocate portal
	 * @param int $advocateId
	 * @param int|null $court
	 * @param int|null $year
	 * @param null|string $result
	 * @return QueryBuilder
	 */
	public function findFromAdvocate(int $advocateId, ?int $court, ?int $year, ?string $result)
	{
		$builder = $this->builder();
		$builder->orderBy('id_case');
		if ($court) {
			$builder->andWhere('"case".court_id = %i', $court);
		}
		if ($year) {
			$builder->andWhere('"case".year = %s', (string) $year);
		}

		$builder->innerJoin('case', 'vw_case_for_advocates', 'vw_case_for_advocates', '"case".id_case = vw_case_for_advocates.id_case');

		$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = "case".id_case');
		$builder->andWhere('vw_latest_tagging_advocate.advocate_id = %i AND vw_latest_tagging_advocate.status = %s', $advocateId, TaggingStatus::STATUS_PROCESSED);

		if ($result) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = "case".id_case');
			$builder->andWhere('vw_latest_tagging_case_result.case_result = %s AND vw_latest_tagging_case_result.status = %s', $result, TaggingStatus::STATUS_PROCESSED);
		}
		return $builder;
	}
}
