<?php
namespace App\Model\Disputes;

use App\Model\Cause\Cause;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Model\Users\User;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$email
 * @property Cause				$case				{m:1 Cause, oneSided=true}
 * @property TaggingCaseResult	$taggingCaseResult  {m:1 TaggingCaseResult, oneSided=true}
 * @property TaggingAdvocate	$taggingAdvocate  {m:1 TaggingAdvocate, oneSided=true}
 * @property string				$reason
 * @property DateTime			$inserted			{default now}
 * @property DateTime|null		$validated
 * @property string				$response
 * @property DateTime|null		$resolved
 * @property User				$resolvedBy			{m:1 User, oneSided=true}
 *
 */
class Dispute extends Entity
{

}