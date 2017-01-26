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

	public function findTaggingResultsByCourt($courtId)
    {
        return $this->connection->query('
        select t.*
        from "tagging_case_result" AS t inner join "case" AS c
        ON (t.case_id = c.id_case AND c.official_data IS NOT NULL AND c.court_id = %i
        )
        
        ',$courtId);
    }

	/**
	 * Returns latest case result tattings of given cases
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