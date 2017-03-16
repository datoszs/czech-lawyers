<?php
namespace App\Enums;


class TaggingStatus
{
	const STATUS_FAILED = 'failed';
	const STATUS_IGNORED = 'ignored';
	const STATUS_PROCESSED = 'processed';

	public static $statuses = [
		self::STATUS_FAILED => 'Selhalo',
		self::STATUS_IGNORED => 'Ignorováno',
		self::STATUS_PROCESSED => 'Zpracováno',
	];
}
