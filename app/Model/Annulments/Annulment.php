<?php declare(strict_types=1);
namespace App\Model\Annulments;

use App\Model\Cause\Cause;
use App\Model\Jobs\JobRun;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int						$id					{primary}
 * @property Cause						$annuledCase		{m:1 Cause, oneSided=true}
 * @property Cause|null					$annulingCase		{m:1 Cause, oneSided=true}
 * @property DateTime					$inserted			{default now}
 * @property DateTime|null				$modified
 * @property JobRun|null				$jobRun				{m:1 JobRun, oneSided=true}
 */
class Annulment extends Entity
{

}
