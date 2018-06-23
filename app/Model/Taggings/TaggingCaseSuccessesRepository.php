<?php
namespace App\Model\Documents;

use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseSuccess;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;


/**
 * @method TaggingCaseSuccess|null getLastTagging($causeId)
 * @method TaggingCaseSuccess[]|null findLatestTagging($causesIds);
 */
class TaggingCaseSuccessesRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [TaggingCaseSuccess::class];
	}
}
