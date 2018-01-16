<?php declare(strict_types=1);
namespace App\Model\Annulments;

use Nextras\Orm\Repository\Repository;

class AnnulmentRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [Annulment::class];
	}
}
