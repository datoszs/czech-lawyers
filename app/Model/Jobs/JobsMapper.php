<?php
namespace App\Model\Jobs;

use Nextras\Orm\Mapper\Mapper;

class JobsMapper extends Mapper
{
	public function getTableName()
	{
		return 'job';
	}

	public function findAllSortedByExecuted()
	{
		return $this->builder()
			->addOrderBy('(SELECT [executed] FROM [job_run] WHERE [job_run.job_id] = [job.id_job] ORDER BY [executed] DESC LIMIT 1) DESC');
	}
}