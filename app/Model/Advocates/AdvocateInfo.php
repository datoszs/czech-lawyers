<?php
namespace App\Model\Advocates;

use App\Model\Jobs\JobRun;
use App\Model\Users\User;
use App\Enums\AdvocateStatus;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Advocate			$advocate			{m:1 Advocate::$advocateInfo}
 * @property string				$status				{enum AdvocateStatus::STATUS_*}
 * @property string				$name
 * @property string				$surname
 * @property string				$degreeBefore
 * @property string				$degreeAfter
 * @property string[]			$email
 * @property string|null		$street
 * @property string|null		$city
 * @property string|null		$postalArea
 * @property string[]			$specialization
 * @property string|null		$company
 * @property string|null		$dataBox
 * @property string|null		$exOffo
 * @property string|null		$wayOfPracticingAdvocacy
 * @property DateTime			$validFrom			{default now}
 * @property DateTime|null		$validTo
 * @property DateTime			$inserted			{default now}
 * @property User				$insertedBy			{m:1 User, oneSided=true}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 */
class AdvocateInfo extends Entity
{

}