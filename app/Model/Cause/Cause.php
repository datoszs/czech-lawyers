<?php
namespace App\Model\Cause;

use App\Model\Jobs\JobRun;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$registrySign
 * @property DateTime			$inserted			{default now}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 */
class Cause extends Entity
{

}