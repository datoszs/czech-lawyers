<?php
namespace App\Utils;


use Nette\Utils\Validators as NetteValidators;

class Helpers
{
	/**
	 * Return exploded string. The function returns empty array when the input string is empty.
	 * Note: for deterministic approach this sort the exploded array.
	 * @param string $delimiter
	 * @param string $input
	 * @return array
	 */
	public static function safeDeterministicExplode($delimiter, $input)
	{
		if (!$input) {
			return [];
		} else {
			$temp = explode($delimiter, $input);
			sort($temp);
			return $temp;
		}
	}

	/**
	 * Return exploded string. The function returns empty array when the input string is empty.
	 * @param string $delimiter
	 * @param string $input
	 * @return array
	 */
	public static function safeExplode($delimiter, $input)
	{
		if (!$input) {
			return [];
		} else {
			$temp = explode($delimiter, $input);
			return $temp;
		}
	}

	/**
	 * Returns true if the variable is array containing only ints (or ints as string). False otherwise
	 * @param array $input
	 * @return true
	 */
	public static function isIntArray($input)
	{
		return is_array($input) && array_reduce($input, function ($carry, $value) {
			return $carry && NetteValidators::isNumericInt($value);
		}, true);
	}
}