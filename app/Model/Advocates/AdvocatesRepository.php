<?php
namespace App\Model\Documents;

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