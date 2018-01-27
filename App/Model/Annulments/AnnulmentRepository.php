<?php
namespace App\Model\Annulments;

use Nextras\Orm\Repository\Repository;
use App\Model\Annulments\Annulment;

/**
 * @method Annulment[]|null findAnnulmentsByCases($causesIds);
 */
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
