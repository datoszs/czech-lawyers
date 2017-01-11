<?php
namespace App\Utils;

use Nette\Utils\Strings;
use Nette\Utils\Validators;

class Normalize
{

	public static function registryMark($registryMark)
	{
		// lower and trim spaces from start/end
		$registryMark = Strings::lower($registryMark);
		$registryMark = Strings::trim($registryMark);
		// ÚS registry marks get special treatment as they come in very weird formats, such as iv.ús vs 4 ús etc.
		if (Strings::contains($registryMark, '.ús ') || Strings::contains($registryMark, ' ús ') || Strings::contains($registryMark, '.ús-st.')) {
			$splitted = Strings::split($registryMark, '/(\.| )/');
			if (count($splitted) > 0 && $splitted[0] !== 'pl' && Validators::isNumericInt($splitted[0])) {
				$splitted[0] = Romans::numberToRoman($splitted[0]);
			}
			if (count($splitted) > 1 && ($splitted[1] === '.' || $splitted[1] === ' ')) {
				$splitted[1] = '. ';
			}
			$registryMark = implode('', $splitted);
		}
		// replace one or more spaces with one
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

	public static function identificationNumber($input)
	{
		return Strings::trim($input);
	}
}
