<?php
namespace App\Model\Cause;

use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$registrySign
 * @property DateTime			$inserted			{default now}
 */
class Cause extends Entity
{

}