<?php
namespace App\Model\Cause;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Repository\Repository;

/**
 * @method ICollection|Cause[] findForResultTagging($courtId)
 * @method ICollection|Cause[] findForSuccessTagging($courtId)
 * @method ICollection|Cause[] findForAdvocateTagging($courtId)
 * @method ICollection|Cause[] findTaggingResultsByCourt($courtId)
 * @method ICollection|Cause[] findTaggingSuccessesByCourt($courtId)
 * @method ICollection|Cause[] search(?string $query, int $start, int $count, string $strategy)
 * @method ICollection|Cause[] findForManualTagging(?int $court, ?string $registryMark, string $advocateFilter, string $resultFilter)
 * @method ICollection|Cause[] findFromAdvocate(int $advocateId, ?int $court, ?int $year, ?string $result)
 * @method Cause|null          getRelevantForAdvocatesById($causeId)
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
