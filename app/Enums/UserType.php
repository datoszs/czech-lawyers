<?php
namespace App\Enums;


class UserType
{
	const TYPE_PERSON = 'person';
	const TYPE_SYSTEM = 'system';

	public static $statuses = [
		self::TYPE_PERSON => 'Osobní účet',
		self::TYPE_SYSTEM => 'Systémový uživatel',
	];
}
