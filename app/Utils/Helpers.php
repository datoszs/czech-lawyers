<?php
namespace App\Utils;


use InvalidArgumentException;
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

	/**
	 * Returns four-letter year determined from registry mark with guessing
	 *
	 * @param string $registryMark
	 * @return int
	 * @throws InvalidArgumentException when error determining year
	 */
	public static function determineYear(string $registryMark) : int
	{
		$temp = explode('/', $registryMark);
		if (count($temp) !== 2) {
			throw new InvalidArgumentException("Invalid registry mark [$registryMark], cannot determine year.");
		}
		if (strlen($temp[1]) === 4 && NetteValidators::isNumericInt($temp[1])) {
			return (int) $temp[1];
		}
		if (strlen($temp[1]) === 2 && NetteValidators::isNumericInt($temp[1])) {
			if ($temp[1] < 92) {
				return 2000 + $temp[1];
			} else {
				return 1900 + $temp[1];
			}
		}
		throw new InvalidArgumentException("Invalid registry mark [$registryMark], unknown error.");
	}
}