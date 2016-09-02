<?php
namespace App\Model\Cause;

use Nextras\Orm\Repository\Repository;

class CausesRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [Cause::class];
	}
}