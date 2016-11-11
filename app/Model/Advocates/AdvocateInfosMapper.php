<?php
namespace App\Model\Advocates;

use Nextras\Orm\Mapper\Mapper;

class AdvocateInfosMapper extends Mapper
{
	public function getTableName()
	{
		return 'advocate_info';
	}
}