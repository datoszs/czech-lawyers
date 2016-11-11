<?php
namespace App\Model\Advocates;

use App\Model\Jobs\JobRun;
use App\Model\Users\User;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Advocate			$advocate			{m:1 Advocate::$advocateInfo}
 * @property string				$hash
 * @property string				$name
 * @property string				$surname
 * @property string				$degreeBefore
 * @property string				$degreeAfter
 * @property string[]			$email
 * @property string|null		$street
 * @property string|null		$city
 * @property string|null		$postalArea
 * @property string[]			$specialization
 * @property DateTime			$validFrom
 * @property DateTime			$validTo
 * @property DateTime			$inserted			{default now}
 * @property User				$insertedBy			{m:1 User, oneSided=true}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 */
class AdvocateInfo extends Entity
{

}