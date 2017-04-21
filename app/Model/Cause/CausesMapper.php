<?php
namespace App\Model\Cause;

use App\Enums\TaggingStatus;
use Mikulas\OrmExt\MappingFactory;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Mapper\Mapper;

class CausesMapper extends Mapper
{
	const INVALID_ADVOCATE = 'invalid_advocate';
	const INVALID_RESULT = 'invalid_result';


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
	 *
	 * @param string $phrase Phrase to be searched
	 * @param int $start Offset of results
	 * @param int $count Number of results
	 * @param string $strategy Matching strategy. Null: %he%, start: he%, end: %he
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
		$builder = $this->builder()->where('registry_sign ILIKE ' . $matchStrategy, $phrase);
		$builder->limitBy($count, $start);
		return $builder;
	}

	public function findForManualTagging(?int $court, bool $onlyDisputed, ?string $filter)
	{
		$builder = $this->builder();
		$builder->orderBy('id_case');
		if ($court) {
			$builder->andWhere('court_id = %i', $court);
		}
		if ($onlyDisputed) {
			// TBD
		}
		if ($filter === static::INVALID_ADVOCATE) {
			$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_advocate.status IS NULL OR (vw_latest_tagging_advocate.status NOT IN (%s) AND NOT vw_latest_tagging_advocate.is_final)', TaggingStatus::STATUS_PROCESSED);

		}
		if ($filter === static::INVALID_RESULT) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_case_result.status IS NULL OR (vw_latest_tagging_case_result.status NOT IN (%s) AND NOT vw_latest_tagging_case_result.is_final)', TaggingStatus::STATUS_PROCESSED);
		}
		return $builder;
	}

	public function findFromAdvocate(int $advocateId, ?int $court, ?int $year, ?string $result)
	{
		$builder = $this->builder();
		$builder->orderBy('id_case');
		if ($court) {
			$builder->andWhere('court_id = %i', $court);
		}
		if ($year) {
			$builder->andWhere('year = %s', (string) $year);
		}

		$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = id_case');
		$builder->andWhere('vw_latest_tagging_advocate.advocate_id = %i AND vw_latest_tagging_advocate.status = %s', $advocateId, TaggingStatus::STATUS_PROCESSED);

		if ($result) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = id_case');
			$builder->andWhere('vw_latest_tagging_case_result.case_result = %s AND vw_latest_tagging_case_result.status = %s', $result, TaggingStatus::STATUS_PROCESSED);
		}
		return $builder;
	}
}
