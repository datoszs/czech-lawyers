<?php
namespace App\Model\Cause;

use App\Model\Court\Court;
use App\Model\Jobs\JobRun;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$registrySign
 * @property Court				$court				{m:1 Court, oneSided=true}
 * @property array|null			$officialData
 * @property DateTime|null		$propositionDate
 * @property DateTime|null		$decisionDate
 * @property DateTime			$inserted			{default now}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 * @property int				$year
 * @property DateTime|null		$modified
 */
class Cause extends Entity
{
	protected function onBeforeUpdate()
	{
		parent::onBeforeUpdate();
		$this->modified = new DateTime();
	}
}
