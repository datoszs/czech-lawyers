<?php

namespace App\Utils;

use App\Exceptions\MultipleMatchesException;
use App\Exceptions\NoMatchException;

class AdvocateMatcher
{
	/** @var array fullname => advocate_id*/
	private $advocates;

	/** @var int */
	private $threshold;

	public function __construct(array $advocates, $threshold)
	{
		$this->advocates = $advocates;
		$this->threshold = $threshold;
	}

	public function match($string)
	{
		// Attempt to check exact match
		if (isset($this->advocates[$string])) {
			return [$string, $this->advocates[$string]];
		}
		// Go through everything to compute safe change
		$belowThreshold = [];
		foreach ($this->advocates as $advocate => $id) {
			//printf("%s with %s = %s\n", $string, $advocate, levenshtein($advocate, $string));
			$lev = levenshtein($advocate, $string);
			if ($lev >= 0 && $lev <= $this->threshold) {
				if (!isset($belowThreshold[$lev])) {
					$belowThreshold[$lev] = [];
				}
				$belowThreshold[$lev][] = [$advocate, $id];
			}
		}
		if (count($belowThreshold) === 0) {
			throw new NoMatchException();
		}
		foreach (range(0, $this->threshold) as $score) {
			if (!isset($belowThreshold[$score])) {
				continue;
			}
			if (count($belowThreshold[$score]) > 1) {
				throw new MultipleMatchesException(sprintf('Potential advocates: [%s] with ids [%s] and distance [%s]', implode(', ', array_column($belowThreshold[$score], 0)), implode(', ', array_column($belowThreshold[$score], 1)), $score));
			}
			return $belowThreshold[$score][0];
		}
		throw new NoMatchException();
	}
}