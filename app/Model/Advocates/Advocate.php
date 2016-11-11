<?php
namespace App\Model\Advocates;

use App\Model\Jobs\JobRun;
use App\Enums\AdvocateStatus;
use App\Model\Users\User;
use DateTime;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int							$id								{primary}
 * @property string							$remoteIdentificator
 * @property string							$identificationNumber
 * @property string							$registrationNumber
 * @property string							$status							{enum AdvocateStatus::STATUS_*}
 * @property OneHasMany|AdvocateInfo[]		$advocateInfo		{1:m AdvocateInfo::$advocate}
 * @property DateTime						$inserted						{default now}
 * @property User							$insertedBy						{m:1 User, oneSided=true}
 * @property JobRun|null					$jobRun							{m:1 JobRun, oneSided=true}
 */
class Advocate extends Entity
{

}