<?php
declare(strict_types=1);

use App\Utils\AdvocatePrefixPrematcher;
use PHPUnit\Framework\TestCase;


final class AdvocatePrefixPrematcherTest extends TestCase
{

	public function testBasic() : void
	{
		$matcher = new AdvocatePrefixPrematcher();
		$this->assertEquals('JUDr. Tomáš Sokol', $matcher->prefixMatch('JUDr. T. S.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Matonoha']));
		$this->assertEquals('JUDr. T. S.', $matcher->prefixMatch('JUDr. T. S.', ['JUDr. Tomáš Ondra Sokol', 'JUDr. Tomáš Matonoha']));
		$this->assertEquals('JUDr. T. S.', $matcher->prefixMatch('JUDr. T. S.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Stěhovavý']));
		$this->assertEquals('JUDr. Tomáš Sokol', $matcher->prefixMatch('JUDr. Tomášem Sokolem.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Matonoha']));
		$this->assertEquals('Mgr. Jan Man', $matcher->prefixMatch('Mgr. Janem Manem.', ['JUDr. Tomáš Sokol', 'Mgr. Jan Man']));
		$this->assertEquals('JUDr. Tomáš Sokol', $matcher->prefixMatch('JUDr. T.  S.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Matonoha']));
		$this->assertEquals('JUDr. Tomáš Sokol', $matcher->prefixMatch('JUDr.T.S.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Matonoha']));
		$this->assertEquals('JUDr. Tomáš Sokol', $matcher->prefixMatch('JUDr. T.S.', ['JUDr. Tomáš Sokol', 'JUDr. Tomáš Matonoha']));
	}
}
