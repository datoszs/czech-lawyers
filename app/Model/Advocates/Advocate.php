<?php
namespace App\Model\Advocates;

use App\Model\Jobs\JobRun;
use App\Model\Users\User;
use App\Utils\Diffable;
use App\Utils\TemplateFilters;
use DateTime;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int							$id								{primary}
 * @property string							$remoteIdentificator
 * @property string							$identificationNumber
 * @property string							$registrationNumber
 * @property string							$localPath
 * @property OneHasMany|AdvocateInfo[]		$advocateInfo					{1:m AdvocateInfo::$advocate, orderBy=[inserted,DESC]}
 * @property DateTime						$inserted						{default now}
 * @property User							$insertedBy						{m:1 User, oneSided=true}
 * @property JobRun|null					$jobRun							{m:1 JobRun, oneSided=true}
 */
class Advocate extends Entity
{
	use Diffable;

	public function getCurrentName(): ?string
	{
		if (!$this->advocateInfo) {
			return null;
		}
		foreach ($this->advocateInfo as $advocateInfo) {
			return TemplateFilters::formatName($advocateInfo->name, $advocateInfo->surname, $advocateInfo->degreeBefore, $advocateInfo->degreeAfter, $advocateInfo->city);
		}
		return null;
	}
}
