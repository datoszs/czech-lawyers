<?php
namespace App\Model\Cause;

use Nextras\Orm\Mapper\Mapper;

class CausesMapper extends Mapper
{
	public function getTableName()
	{
		return 'case';
	}
}