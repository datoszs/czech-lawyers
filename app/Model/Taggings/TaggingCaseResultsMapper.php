<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class TaggingCaseResultsMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_case_result';
	}

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
}