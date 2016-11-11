<?php
namespace App\Model\Advocates;

use Nextras\Orm\Mapper\Mapper;

class AdvocatesMapper extends Mapper
{
	public function getTableName()
	{
		return 'advocate';
	}
}