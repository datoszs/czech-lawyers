<?php
namespace App\Model\Cause;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|Cause[] findForTagging($courtId)
 * @method ICollection|Cause[] search($phrase, $limit = null, $match = null)
 */
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