<?php
namespace App\Model\Documents;

use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Dbal\Result\Result;
use Nextras\Orm\Mapper\Mapper;

class TaggingAdvocatesMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_advocate';
	}

	/**
	 * Returns latest advocate tagging for given case
	 *
	 * @param int $causeId
	 * @return QueryBuilder
	 */
	public function getLatestTagging($causeId)
	{
		return $this->builder()->where('case_id = %i', $causeId)->orderBy('inserted DESC')->limitBy(1);
	}

	/**
	 * Returns latest advocate taggings for given advocates.
	 *
	 * @param int[] $advocatesIds
	 * @return Result|NULL
	 */
	public function findLatestTaggingByAdvocates(array $advocatesIds)
	{
		return $this->connection->query('
			SELECT
				*
			FROM vw_latest_tagging_advocate
			WHERE advocate_id IN %i[]
		', $advocatesIds); // DO NOT PUSH WHERE INSIDE as it changes semantic and provides completely different results.
	}

	/**
	 * Returns latest advocate taggings for given cases.
	 *
	 * @param int[] $causesIds
	 * @return Result|NULL
	 */
	public function findLatestTagging(array $causesIds)
	{
		if (count($causesIds) == 0) {
			$causesIds[] = null;
		}
		return $this->connection->query('
			SELECT * FROM vw_latest_tagging_advocate WHERE case_id IN %?i[]
		', $causesIds);
	}
}
