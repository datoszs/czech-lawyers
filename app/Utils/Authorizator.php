<?php
namespace App\Utils;


use App\Enums\UserRole;
use Nette\Security\Permission;

class Authorizator extends Permission
{

	public function __construct()
	{
		$this->addRole(UserRole::TYPE_GUEST);
		$this->addRole(UserRole::TYPE_VIEWER, UserRole::TYPE_GUEST);
		$this->addRole(UserRole::TYPE_ADMIN, UserRole::TYPE_VIEWER);

		$this->addResource(Resources::SHARED);

		$this->addResource(Resources::ADVOCATES);
		$this->addResource(Resources::DOCUMENTS);
		$this->addResource(Resources::TAGGING);

		$this->addResource(Resources::USERS);
		$this->addResource(Resources::LOGS);

		$this->deny(UserRole::TYPE_GUEST);
		$this->allow(UserRole::TYPE_GUEST, Resources::SHARED, Actions::LOGIN);

		$this->allow(UserRole::TYPE_VIEWER, Resources::SHARED, Actions::PROFILE);
		$this->allow(UserRole::TYPE_VIEWER, [Resources::ADVOCATES, Resources::DOCUMENTS, Resources::TAGGING], [Actions::VIEW]);

		$this->allow(UserRole::TYPE_ADMIN);
	}
}