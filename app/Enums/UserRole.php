<?php
namespace App\Enums;


class UserRole
{
	const TYPE_GUEST = 'guest';
	const TYPE_VIEWER = 'viewer';
	const TYPE_ADMIN = 'admin';

	static $statuses = [
		self::TYPE_GUEST => 'Host',
		self::TYPE_VIEWER => 'Pozorovatel',
		self::TYPE_ADMIN => 'Administrátor',
	];
}