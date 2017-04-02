<?php
namespace App\Utils;



use Nette\Utils\Strings;

class TemplateFilters
{
	public static function common($filter, $value)
	{
		if (method_exists(__CLASS__, $filter)) {
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array([__CLASS__, $filter], $args);
		}
	}

	/**
	 * Formats given name according to czech grammar
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param null|string $degreeBefore
	 * @param null|string $degreeAfter
	 * @param null|string $city
	 * @return string
	 */
	public static function formatName(string $firstname, string $lastname, ?string $degreeBefore = null, ?string $degreeAfter = null, ?string $city = null)
	{
		$output = [];
		$output[] = $degreeBefore;
		$output[] = $firstname;
		$output[] = $lastname;
		$temp = implode(' ', array_filter($output));
		if ($degreeAfter) {
			$temp .= ', ' . $degreeAfter;
		}
		if ($city) {
			$temp .= ' - '. $city;
		}
		return $temp;
	}

	/**
	 * Formats given normalized (canonized) registry mark to appropriate format
	 * See: https://github.com/datoszs/czech-lawyers/issues/91
	 * @param string $value
	 * @return string
	 */
	public static function formatRegistryMark(string $value) : string
	{
		$parts = explode(' ', $value);
		if (count($parts) !== 3) {
			return $value;
		}

		if ($parts[0] === 'pl.') { // Exception of Law court
			$parts[0] = 'Pl.';
		} else {
			$parts[0] = Strings::upper($parts[0]);
		}

		if ($parts[1] === 'ús') { // Exception of Law court
			$parts[1] = 'ÚS';
		} else if ($parts[1] === 'icdo') { // Exception of Supreme court
			$parts[1] = 'ICdo';
		} else if ($parts[1] === 'nscr') { // Exception of Supreme court
			$parts[1] = 'NSČR';
		} else {
			$parts[1] = Strings::firstUpper($parts[1]);
		}


		return implode(' ', $parts);
	}
}
