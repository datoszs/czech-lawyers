<?php
namespace App\Model\Cause;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Mapper\Mapper;

class CausesMapper extends Mapper
{
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
	 * Returns all cases which can be tagged (the cases with is_final flag set to true are ignored).
	 *
	 * @param int $courtId
	 * @return QueryBuilder
	 */
	public function findForTagging($courtId)
	{
		return $this->builder()->where('court_id = %i AND id_case NOT IN (SELECT case_id FROM tagging_case_result WHERE is_final)', $courtId);
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
}