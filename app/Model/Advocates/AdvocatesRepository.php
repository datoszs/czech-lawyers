<?php
namespace App\Model\Advocates;

use Nextras\Orm\Repository\Repository;

class AdvocatesRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [Advocate::class];
	}
}