<?php declare(strict_types=1);

use App\Utils\AdvocateMatcher;
use PHPUnit\Framework\TestCase;


final class AdvocateMatcherTest extends TestCase
{

	public function testWithoutInflectionMatcher() : void
	{
		// The same
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Jan Novák', 'Jan Novák'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Jana Nováková', 'Jana Nováková'));

		// Inflected
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Jan Novák', 'Janem Novákem'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Jan Žák', 'Janem Žákem'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Ivan Levego', 'Ivanem Levegem'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Tomáš Káně', 'Tomášem Kánětem'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Petr Váňa', 'Petrem Váňou'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Ivana Lidová', 'Ivanou Lidovou'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Kamila Klvačová', 'Kamilou Klvačovou'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Karel Goláň', 'Karlem Goláněm'));
		$this->assertTrue(AdvocateMatcher::matchWordsWithoutInflection('Anna Františková', 'Annou Františkovou'));

		// Too short for matching
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Iva Lidová', 'Ivou Lidovou'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Iva Suchá', 'Ivou Suchou'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Ivo Lego', 'Ivem Legem'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Jan Mák', 'Janem Žákem'));

		// Totaly different
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Anna Františková', 'Annou Frantová'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Anna Františková', 'Aneta Františkovou'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Jan Františková', 'Jenem Františkovou'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Jaroslav Faněk', 'Jaroslav Vaněk'));
		$this->assertFalse(AdvocateMatcher::matchWordsWithoutInflection('Jaroslav Faněk', 'Jaroslavem Vankěm'));
	}
}
