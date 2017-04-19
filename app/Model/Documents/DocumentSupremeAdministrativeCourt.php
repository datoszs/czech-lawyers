<?php
namespace App\Model\Documents;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Document			$document			{m:1 Document, oneSided=true}
 * @property string				$orderNumber
 * @property string				$decision
 * @property string				$decisionType
 * @property array|null			$sides
 * @property array|null			$prejudicate
 * @property string|null		$complaint
 */
class DocumentSupremeAdministrativeCourt extends Entity
{

}
