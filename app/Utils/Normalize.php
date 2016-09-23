<?php
namespace App\Utils;

use Nette\Utils\Strings;

class Normalize
{

	public static function registryMark($registryMark)
	{
		$registryMark = Strings::lower($registryMark);
		$registryMark = Strings::trim($registryMark);
		$registryMark = preg_replace('!\s+!', ' ', $registryMark);
		return $registryMark;
	}

	public static function username($input)
	{
		return Strings::lower($input);
	}

	public static function recordId($input)
	{
		return preg_replace('!\s+!', ' ', Strings::trim(Strings::upper($input)));
	}
}
