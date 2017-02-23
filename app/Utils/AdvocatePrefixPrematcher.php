<?php
declare(strict_types=1);

namespace App\Utils;

class AdvocatePrefixPrematcher
{

	/**
	 * Goes through all given advocates names (in nominative) and tries to match given name by initiales.
	 * Return original value when no match is found.
	 *
	 * Notes:
	 * - Works with J.S. and J. S.
	 * - Expects not that many advocates (below 10)
	 * - Names has to have same amount of components
	 * - All names (all substrings) has to match by prefix of at least 1 character
	 * - Only one name has to be matched
	 *
	 * @param string $name
	 * @param array $advocates
	 * @return string
	 */
	public function prefixMatch(string $name, array $advocates) : string
	{
		if (!$name || count($advocates) === 0) {
			return $name;
		}
		$parts = $this->prepareName($name);
		$candidates = [];
		foreach ($advocates as $advocate) {
			$advocateParts = $this->prepareName($advocate);
			if (count($parts) !== count($advocateParts)) {
				continue;
			}
			$match = true;
			for ($i = 0, $iMax = count($parts); $i < $iMax; $i++) {
				if (count($parts[$i]) >=3) { // probably full name in other than nominative (use longer prefix to match)
					if (array_splice($advocateParts[$i], 0, 3) !== array_splice($parts[$i], 0, 3)) { // chosen after consulting database
						$match = false;
					}
				} else { // probably only initials
					if ($advocateParts[$i][0] !== $parts[$i][0]) {
						$match = false;
					}
				}
			}
			if ($match) {
				$candidates[] = $advocate;
			}
		};
		if (count($candidates) === 1) {
			return $candidates[0];
		}
		return $name;
	}

	private function prepareName(string $name) : array
	{
		return array_values(array_filter(preg_split('/[ .]/', preg_replace('!\s+!', ' ', $name))));
	}
}
