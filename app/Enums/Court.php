<?php
namespace App\Enums;


class Court
{
	const TYPE_NSS = 1;
	const TYPE_NS = 2;
	const TYPE_US = 3;

	public static $types = [
		'nss' => self::TYPE_NSS,
		'ns' => self::TYPE_NS,
		'us' => self::TYPE_US
	];
}