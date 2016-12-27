<?php
namespace App\Model\Documents;

use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use Nextras\Orm\Repository\Repository;


/**
 * @method TaggingCaseResult|null getLastTagging($causeId)
 */
class TaggingCaseResultsRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [TaggingCaseResult::class];
	}
}