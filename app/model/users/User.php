<?php
namespace App\Model\Users;

use DateTime;
use App\Enums\UserType;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property string				$type				{enum UserType::TYPE_*}
 * @property string				$username
 * @property string|null		$password
 * @property string|null		$role
 * @property bool				$isActive
 * @property bool				$isLoginAllowed
 * @property DateTime			$inserted			{default now}
 * @property DateTime|null		$updated
 */
class User extends Entity
{

}