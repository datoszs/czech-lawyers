<?php
namespace App\Enums;


class TaggingStatus
{
	const STATUS_FAILED = 'failed';
	const STATUS_IGNORED = 'ignored';
	const STATUS_PROCESSED = 'processed';
	const STATUS_FUZZY = 'fuzzy';

	static $statuses = [
		self::STATUS_FAILED => 'Selhalo',
		self::STATUS_IGNORED => 'Ignorováno',
		self::STATUS_PROCESSED => 'Zpracováno',
		self::STATUS_FUZZY => 'Fuzzy',
	];
}