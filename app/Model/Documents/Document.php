<?php
namespace App\Model\Documents;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Court				$court				{m:1 Court, oneSided=true}
 * @property Cause				$case				{m:1 Cause, oneSided=true}
 * @property DateTime			$decisionDate
 * @property string				$localPath
 * @property string				$webPath
 * @property DateTime			$inserted			{default now}
 */
class Document extends Entity
{

}