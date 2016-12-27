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
}