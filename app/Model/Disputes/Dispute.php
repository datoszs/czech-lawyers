<?php
namespace App\Model\Disputes;

use App\Model\Cause\Cause;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Model\Users\User;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int						$id					{primary}
 * @property string						$fullname
 * @property string						$email
 * @property string						$code
 * @property Cause						$case				{m:1 Cause, oneSided=true}
 * @property TaggingCaseResult|null 	$taggingCaseResult  {m:1 TaggingCaseResult, oneSided=true}
 * @property TaggingAdvocate|null		$taggingAdvocate  {m:1 TaggingAdvocate, oneSided=true}
 * @property bool						$taggingCaseResultDisputed
 * @property bool						$taggingAdvocateDisputed
 * @property string						$reason
 * @property DateTime					$inserted			{default now}
 * @property DateTime					$validUntil
 * @property DateTime|null				$validatedAt
 * @property string|null				$response
 * @property DateTime|null				$resolved
 * @property User|null					$resolvedBy			{m:1 User, oneSided=true}
 *
 */
class Dispute extends Entity
{

}
