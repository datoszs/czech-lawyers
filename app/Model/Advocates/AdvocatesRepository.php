<?php
namespace App\Model\Advocates;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|Advocate[] search($phrase, $limit = null)
 */
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