<?php
namespace App\Model\Disputes;

use Nextras\Orm\Repository\Repository;

class DisputeRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [Dispute::class];
	}
}
