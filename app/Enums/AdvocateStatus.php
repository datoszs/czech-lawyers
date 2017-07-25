<?php
namespace App\Enums;


class AdvocateStatus
{
	const STATUS_ACTIVE = 'active';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_REMOVED = 'removed';
	const STATUS_CREATED = 'created';

	static $statuses = [
		self::STATUS_ACTIVE => 'Aktivní',
		self::STATUS_SUSPENDED => 'Pozastavený',
		self::STATUS_REMOVED => 'Neaktivní', // originally: Vyškrtnutý
		self::STATUS_CREATED => 'Vytvořený',
	];
}
