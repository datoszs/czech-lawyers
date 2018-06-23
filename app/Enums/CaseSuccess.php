<?php
namespace App\Enums;


class CaseSuccess
{
	const RESULT_NEUTRAL = 'neutral';
	const RESULT_POSITIVE = 'positive';
	const RESULT_NEGATIVE = 'negative';
	const RESULT_UNKNOWN = 'unknown';

	static $statuses = [
		self::RESULT_NEUTRAL => 'Neutrální',
		self::RESULT_POSITIVE => 'Pozitivní',
		self::RESULT_NEGATIVE => 'Negativní',
		self::RESULT_UNKNOWN => 'Neznámý',
	];
}
