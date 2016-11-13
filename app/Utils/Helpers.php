<?php
namespace App\Utils;


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
}