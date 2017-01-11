<?php
namespace App\Utils;

/**
 * Adapted from http://www.hashbangcode.com/blog/php-function-turn-integer-roman-numerals
 */
class Romans
{

	/**
	 * Returns input number (int) in roman numeral
	 * @param mixed $integer
	 * @param bool $lowercase
	 * @return string
	 */
	public static function numberToRoman($integer, $lowercase = true) : string
	{
		$lookup = [
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1
		];
		$integer = intval($integer);
		$result = '';
		foreach($lookup as $roman => $value){
			// Determine the number of matches
			$matches = intval($integer / $value);

			// Add the same number of characters to the string
			$result .= str_repeat($roman, $matches);

			// Set the integer to be the remainder of the integer and the value
			$integer = $integer % $value;
		}

		// The Roman numeral should be built, return it
		if ($lowercase) {
			return strtolower($result);
		}
		return $result;
	}
}