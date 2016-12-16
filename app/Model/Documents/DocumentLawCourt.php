<?php
namespace App\Model\Documents;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Document			$document			{m:1 Document, oneSided=true}
 * @property string				$ecli
 * @property string				$formDecision
 * @property string				$decisionResult
 */
class DocumentLawCourt extends Entity
{

}
