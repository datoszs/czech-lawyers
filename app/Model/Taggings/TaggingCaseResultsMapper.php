<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class TaggingCaseResultsMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_case_results';
	}
}