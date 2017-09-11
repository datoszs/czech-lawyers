<?php

namespace App\Utils;

use App\Exceptions\MultipleMatchesException;
use App\Exceptions\NoMatchException;
use Inflection;

class AdvocateMatcher
{

	/** @var array fullname => [advocate_id, advocate_id] */
	private $advocates = [];

	/** @var array instrumental => nominativ */
	private $instrumentals = [];

	public function __construct(array $advocates)
	{
		$inflection = new Inflection();
		foreach ($advocates as $fullname => $advocates_ids) {
			$this->advocates[$fullname] = explode(' ', $advocates_ids);
			$this->instrumentals[$inflection->inflect($fullname)[7]] = $fullname;
		}
	}

	public function match(string $string)
	{
		$originalString = $string;

		// Remove degree if present from input string (only in that)
		$string = Degree::removeBefore($string);
		$string = Degree::removeAfter($string);

		// Attempt to check exact match
		if (isset($this->advocates[$string])) {
			if (count($this->advocates[$string]) > 1) {
				throw new MultipleMatchesException(sprintf('Potential advocates: [%s] with ids [%s] using direct match.', implode(', ', array_fill(0, count($this->advocates[$string]), $string)), implode(', ', $this->advocates[$string])));
			}
			return [$string, $this->advocates[$string][0]];
		}

		// Attempt to check instrumental.
		if (isset($this->instrumentals[$string])) {
			$nominativ = $this->instrumentals[$string];
			if (isset($this->advocates[$nominativ])) {
				if (count($this->advocates[$nominativ]) > 1) {
					throw new MultipleMatchesException(sprintf('Potential advocates: [%s] with ids [%s] using inflection to instrumental.', implode(', ', array_fill(0, count($this->advocates[$nominativ]), $nominativ)), implode(', ', $this->advocates[$nominativ])));
				}
				return [$nominativ, $this->advocates[$nominativ][0]];
			}
		}
		// Fallback variant
		// Iterate all and gather all which pass comparison without inflected suffix
		$candidates = [];
		$candidatesNames = [];
		foreach ($this->advocates as $advocate => $ids) {
			if (static::matchWordsWithoutInflection($advocate, $string)) {
				array_push($candidates, ... $ids);
				array_push($candidatesNames, ... array_fill(0, count($ids), $advocate));
			}
		}
		if (count($candidates) === 1) {
			return [$candidatesNames[0], $candidates[0]];
		}
		if (count($candidates) > 1) {
			throw new MultipleMatchesException(sprintf('Potential advocates: [%s] with ids [%s] using match without inflected suffix.', implode(', ', $candidatesNames), implode(', ', $candidates)));
		}
		throw new NoMatchException();
	}

	public static function matchWordsWithoutInflection(string $nominativ, string $nominativOrInstrumental): bool
	{
		$status = true;
		$nominativ = explode(' ', $nominativ);
		$nominativOrInstrumental = explode(' ', $nominativOrInstrumental);
		if (count($nominativ) !== count($nominativOrInstrumental)) {
			return false;
		}
		foreach ($nominativ as $key => $word1) {
			$word2 = $nominativOrInstrumental[$key];
			$status = $status && static::matchWordWithoutInflection($word1, $word2);
		}
		return $status;
	}

	public static function matchWordWithoutInflection(string $nominativ, string $nominativOrInstrumental): bool
	{
		$length1 = mb_strlen($nominativ);
		$length2 = mb_strlen($nominativOrInstrumental);
		// Ignored: Faněk -> Faňkem
		// Masculine: -em -tem
		// Femine: -ou -ovou
		// When words differing for more than 3 chars, it is not (probably) inflected; also when nominative is longer than instrumental (or nominative) that seems untypical.
		if ($length2 - $length1 > 3 || $length2 - $length1 < 0) {
			return false;
		}
		// Rest has to match (except one character which can change), minimum length for matching is 3 characters
		$ignored = ($length2 - $length1) === 3 ? 3 : (($length2 - $length1) + 1);
		$matchedLength = max($length1 - $ignored, 3);
		return mb_substr($nominativ, 0, $matchedLength) === mb_substr($nominativOrInstrumental, 0, $matchedLength);
	}
}
