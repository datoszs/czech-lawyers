<?php
namespace App\Utils;

use Nette\Utils\Strings;
use Nette\Utils\Validators as NetteValidators;

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
			if (count($splitted) > 0 && $splitted[0] !== 'pl' && NetteValidators::isNumericInt($splitted[0])) {
				$splitted[0] = Romans::numberToRoman($splitted[0]);
			}
			if (count($splitted) > 1 && ($splitted[1] === '.' || $splitted[1] === ' ')) {
				$splitted[1] = '. ';
			}
			$registryMark = implode('', $splitted);
		}
		// replace one or more spaces with one
		$registryMark = preg_replace('!\s+!', ' ', $registryMark);
		// replace nsčr with nscr
		$registryMark = preg_replace('! nsčr !', ' nscr ', $registryMark);
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

	/**
	 * Fix names like "Mgr.et Mgr. Novák Jan Ph.D., LL.M." and "Mgr.et Mgr. Nováková Štastná Lucie Ph.D., LL.M."
	 * to <degree before> <name> <surname> <degree after>
	 * @param string $input
	 * @return string
	 */
	public static function fixSurnameName(string $input): string
	{
		$degreeBefore = Degree::extractBefore($input);
		$degreeAfter = Degree::extractAfter($input);
		$degreeBeforeLength = $degreeBefore ? mb_strlen($degreeBefore) + 1 : 0;
		$degreeAfterLength = $degreeAfter ? mb_strlen($degreeAfter) + 1 : 0;
		$input = mb_substr($input, $degreeBeforeLength, mb_strlen($input) - ($degreeAfterLength + $degreeBeforeLength));
		$temp = explode(' ', $input);
		$words = count($temp);
		if ($words >= 2) {
			$last = $temp[$words - 1];
			unset($temp[$words - 1]);
			array_unshift($temp, $last);
		}
		return ($degreeBefore ? ($degreeBefore . ' ') : '') .
				implode(' ', $temp) .
				($degreeAfter ? (' ' . $degreeAfter) : '');
	}
}
