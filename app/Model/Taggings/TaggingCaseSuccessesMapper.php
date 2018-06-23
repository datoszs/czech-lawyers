<?php

namespace App\Model\Documents;

use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Dbal\Result\Result;
use Nextras\Orm\Mapper\Mapper;

class TaggingCaseSuccessesMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_case_success';
	}

	/**
	 * Returns latest case success tagging of given case
	 *
	 * @param int $causeId
	 * @return QueryBuilder
	 */
	public function getLastTagging($causeId)
	{
		return $this->builder()->where('case_id = %i', $causeId)->orderBy('inserted DESC')->limitBy(1);
	}

	/**
	 * Returns latest case success taggings of given cases
	 *
	 * @param int[] $causesIds
	 * @return Result|NULL
	 */
	public function findLatestTagging(array $causesIds)
	{
		return $this->connection->query('
			SELECT * FROM vw_latest_tagging_case_success WHERE case_id IN %i[]
		', $causesIds);
	}
}
