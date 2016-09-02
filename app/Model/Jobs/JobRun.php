<?php
namespace App\Model\Jobs;

use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Job				$job				{m:1 Job::$runs}
 * @property int|null			$returnCode
 * @property string|null		$output
 * @property string|null		$message
 * @property DateTime			$executed
 * @property DateTime|null		$finished
 */
class JobRun extends Entity
{

}