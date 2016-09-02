<?php
namespace App\Model\Jobs;

use Nextras\Orm\Mapper\Mapper;

class JobRunsMapper extends Mapper
{
	public function getTableName()
	{
		return 'job_run';
	}
}