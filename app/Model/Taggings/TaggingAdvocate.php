<?php
namespace App\Model\Taggings;

use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Documents\Document;
use App\Model\Jobs\JobRun;
use App\Model\Users\User;
use App\Enums\TaggingStatus;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Cause				$case				{m:1 Cause, oneSided=true}
 * @property Document|null		$document			{m:1 Document, oneSided=true}
 * @property Advocate|null		$advocate			{m:1 Advocate, oneSided=true}
 * @property string				$status				{enum TaggingStatus::STATUS_*}
 * @property boolean			$isFinal
 * @property string|null		$debug
 * @property DateTime			$inserted			{default now}
 * @property User				$insertedBy			{m:1 User, oneSided=true}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 */
class TaggingAdvocate extends Entity
{

}
