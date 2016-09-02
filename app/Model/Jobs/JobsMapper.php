<?php
namespace App\Model\Jobs;

use Nextras\Orm\Mapper\Mapper;

class JobsMapper extends Mapper
{
	public function getTableName()
	{
		return 'job';
	}
}