<?php
namespace App\Model\Advocates;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|Advocate[] findFromDecile(int $decile, int $start, int $count, bool $reverse)
 * @method ICollection|Advocate[] search($phrase, int $start = 0, ?int $limit = null)
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
