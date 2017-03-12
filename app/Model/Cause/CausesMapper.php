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
	 * @param int|null $limit Maximal number of results, more than some threashold will result in memory limit problems
	 * @param string|null $match Mathing strategy. Null: %he%, start: he%, end: %he
	 * @return QueryBuilder
	 */
	public function search($phrase, $limit = null, $match = null)
	{
		if ($match === 'start') {
			$matchStrategy = '%like_';
		} elseif ($match === 'end') {
			$matchStrategy = '%_like';
		} else {
			$matchStrategy = '%_like_';
		}
		$builder = $this->builder()->where('registry_sign ILIKE ' . $matchStrategy, $phrase);
		if ($limit) {
			$builder->limitBy($limit);
		}
		return $builder;
	}

	public function findForManualTagging(?int $court, bool $onlyDisputed, ?string $filter)
	{
		$builder = $this->builder();
		$builder->orderBy('id_case');
		if ($court) {
			$builder->where('court_id = %i', $court);
		}
		if ($onlyDisputed) {
			// TBD
		}
		if ($filter === static::INVALID_ADVOCATE) {
			$builder->leftJoin('case', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate', 'vw_latest_tagging_advocate.case_id = id_case');
			$builder->where('vw_latest_tagging_advocate.status IS NULL OR (vw_latest_tagging_advocate.status NOT IN (%s) AND NOT vw_latest_tagging_advocate.is_final)', TaggingStatus::STATUS_PROCESSED);

		}
		if ($filter === static::INVALID_RESULT) {
			$builder->leftJoin('case', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result', 'vw_latest_tagging_case_result.case_id = id_case');
			$builder->where('vw_latest_tagging_case_result.status IS NULL OR (vw_latest_tagging_case_result.status NOT IN (%s) AND NOT vw_latest_tagging_case_result.is_final)', TaggingStatus::STATUS_PROCESSED);
		}
		return $builder;
	}
}
