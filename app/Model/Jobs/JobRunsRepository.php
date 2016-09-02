<?php
namespace App\Model\Jobs;

use Nextras\Orm\Repository\Repository;

class JobRunsRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [JobRun::class];
	}
}