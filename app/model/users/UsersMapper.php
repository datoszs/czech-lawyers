<?php
namespace App\Model\Users;

use Nextras\Orm\Mapper\Mapper;

class UsersMapper extends Mapper
{

	public function getTableName()
	{
		return 'user';
	}
}