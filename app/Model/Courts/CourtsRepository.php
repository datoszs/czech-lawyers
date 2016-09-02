<?php
namespace App\Model\Court;

use App\Enums\Court as CourtEnum;
use Nextras\Orm\Repository\Repository;

class CourtsRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [Court::class];
	}
}