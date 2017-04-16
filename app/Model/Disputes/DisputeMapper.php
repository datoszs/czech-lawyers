<?php
namespace App\Model\Disputes;


use Nextras\Orm\Mapper\Mapper;

class DisputeMapper extends Mapper
{

	public function getTableName()
	{
		return 'case_disputation';
	}
}
