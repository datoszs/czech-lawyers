<?php
namespace App\Model\Cause;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|Cause[] findForResultTagging($courtId)
 * @method ICollection|Cause[] findForAdvocateTagging($courtId)
 * @method ICollection|Cause[] findTaggingResultsByCourt($courtId)
 * @method ICollection|Cause[] search(?string $query, int $start, int $count, string $strategy)
 * @method ICollection|Cause[] findForManualTagging(?int $court, bool $onlyDisputed, string $filter)
 * @method ICollection|Cause[] findFromAdvocate(int $advocateId, ?int $court, ?int $year, ?string $result)
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
