<?php
namespace App\Enums;


class AdvocateStatus
{
	const STATUS_ACTIVE = 'active';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_REMOVED = 'removed';

	static $statuses = [
		self::STATUS_ACTIVE => 'Aktivní',
		self::STATUS_SUSPENDED => 'Pozastavený',
		self::STATUS_REMOVED => 'Vyškrtnutý',
	];
}