<?php
namespace App\Model\Jobs;

use App\Model\Users\User;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int					$id					{primary}
 * @property string					$name
 * @property string|null			$description
 * @property User					$databaseUser		{m:1 User, oneSided=true}
 * @property OneHasMany|JobRun[]	$runs				{1:m JobRun::$job, orderBy=[executed, DESC]}
 */
class Job extends Entity
{

}