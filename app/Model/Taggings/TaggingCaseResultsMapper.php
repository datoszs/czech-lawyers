<?php
namespace App\Model\Documents;

use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Dbal\Result\Result;
use Nextras\Orm\Mapper\Mapper;

class TaggingCaseResultsMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_case_result';
	}

	/**
	 * Returns latest case result tagging of given case
	 *
	 * @param int $causeId
	 * @return QueryBuilder
	 */
	public function getLastTagging($causeId)
	{
		return $this->builder()->where('case_id = %i', $causeId)->orderBy('inserted DESC')->limitBy(1);
	}

	/**
	 * Returns latest case result taggings of given cases
	 *
	 * @param int[] $causesIds
	 * @return Result|NULL
	 */
	public function findLatestTagging(array $causesIds)
	{
		return $this->connection->query('
			SELECT DISTINCT ON (case_id) * FROM "tagging_case_result" WHERE case_id IN %i[] ORDER BY case_id, inserted DESC
		', $causesIds);
	}
}