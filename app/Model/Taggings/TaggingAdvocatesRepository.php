<?php
namespace App\Model\Documents;

use App\Model\Taggings\TaggingAdvocate;
use Nextras\Orm\Repository\Repository;

/**
 * @method TaggingAdvocate|null getLatestTagging($causeId)
 * @method TaggingAdvocate[]|null findLatestTagging($causesIds);
 * @method TaggingAdvocate[]|null findLatestTaggingByAdvocates($advocatesIds);
 */
class TaggingAdvocatesRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [TaggingAdvocate::class];
	}
}