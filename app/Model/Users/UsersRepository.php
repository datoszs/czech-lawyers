<?php
namespace App\Model\Users;

use Nextras\Orm\Repository\Repository;

class UsersRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [User::class];
	}
}