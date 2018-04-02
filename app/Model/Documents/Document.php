<?php
namespace App\Model\Documents;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use App\Model\Jobs\JobRun;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$recordId
 * @property Court				$court				{m:1 Court, oneSided=true}
 * @property Cause				$case				{m:1 Cause, oneSided=true}
 * @property DateTime			$decisionDate
 * @property string				$localPath
 * @property string				$webPath
 * @property DateTime			$inserted			{default now}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 * @property DateTime|null		$modified
 */
class Document extends Entity
{

	public function isAvailable()
	{
		return isset($this->localPath, $this->webPath) && $this->localPath && $this->webPath;
	}

	protected function onBeforeUpdate()
	{
		parent::onBeforeUpdate();
		$this->modified = new DateTime();
	}
}
