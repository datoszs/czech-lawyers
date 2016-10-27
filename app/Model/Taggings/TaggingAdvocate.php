<?php
namespace App\Model\Taggings;

use App\Model\Documents\Advocate;
use App\Model\Documents\Document;
use App\Model\Jobs\JobRun;
use App\Model\Users\User;
use App\Enums\TaggingStatus;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Document			$document			{m:1 Document, oneSided=true}
 * @property Advocate|null		$advocate			{m:1 Advocate, oneSided=true}
 * @property string				$status				{enum TaggingStatus::STATUS_*}
 * @property boolean			$isFinal
 * @property string|null		$debug
 * @property DateTime			$inserted			{default now}
 * @property User				$insertedBy			{m:1 User, oneSided=true}
 * @property JobRun|null		$jobRun				{m:1 JobRun, oneSided=true}
 * @property DateTime			$updated			{default now}
 */
class TaggingAdvocate extends Entity
{

}