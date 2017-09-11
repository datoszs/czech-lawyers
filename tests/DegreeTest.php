<?php declare(strict_types=1);

use App\Utils\Degree;
use PHPUnit\Framework\TestCase;


final class DegreeTest extends TestCase
{

	public function testExtractBefore(): void
	{
		$this->assertSame('JUDr.', Degree::extractBefore('JUDr. Jan Novák'));
		$this->assertSame('JuDr.', Degree::extractBefore('JuDr. Jan Novák'));
		$this->assertSame('Mgr. et. Mgr.', Degree::extractBefore('Mgr. et. Mgr. Zůbek Ondřej'));
		$this->assertSame('Prom.práv.', Degree::extractBefore('Prom.práv. František Omáčka'));
		$this->assertSame('Doc. JUDr. Ing.', Degree::extractBefore('Doc. JUDr. Ing. Sokol Jan'));
		$this->assertSame('JUDr.Mgr.', Degree::extractBefore('JUDr.Mgr. Sokol Jan'));
		$this->assertSame('JUDr. Mgr.', Degree::extractBefore('JUDr. Mgr. Sokol Jan'));
		$this->assertSame('Mag', Degree::extractBefore('Mag Sokol Jan'));
		$this->assertSame('Bc. Mgr. et Mgr.', Degree::extractBefore('Bc. Mgr. et Mgr. Sokol Jan'));
	}

	public function testRemoveBefore(): void
	{
		$this->assertSame('Jan Novák', Degree::removeBefore('JUDr. Jan Novák'));
		$this->assertSame('Jan Novák', Degree::removeBefore('JuDr. Jan Novák'));
		$this->assertSame('Zůbek Ondřej', Degree::removeBefore('Mgr. et. Mgr. Zůbek Ondřej'));
		$this->assertSame('František Omáčka', Degree::removeBefore('Prom.práv. František Omáčka'));
		$this->assertSame('Sokol Jan', Degree::removeBefore('Doc. JUDr. Ing. Sokol Jan'));
		$this->assertSame('Sokol Jan', Degree::removeBefore('JUDr.Mgr. Sokol Jan'));
		$this->assertSame('Sokol Jan', Degree::removeBefore('JUDr. Mgr. Sokol Jan'));
		$this->assertSame('Sokol Jan', Degree::removeBefore('Mag Sokol Jan'));
		$this->assertSame('Tereza Nováková-Štastná', Degree::removeBefore('Bc. Mgr. et Mgr. Tereza Nováková-Štastná'));
		$this->assertSame('Anežka Přikrylová Svatá', Degree::removeBefore('Bc. Mgr. et Mgr. Anežka Přikrylová Svatá'));
	}

	public function testExtractAfter(): void
	{
		$this->assertSame('Ph.D., LL.M.', Degree::extractAfter('Mgr.et Mgr. Novák Jan Ph.D., LL.M.'));
		$this->assertSame('Ph.D.', Degree::extractAfter('JuDr. Jan Novák Ph.D.'));
		$this->assertSame('PhD. MBA', Degree::extractAfter('Mgr. et. Mgr. Zůbek Ondřej PhD. MBA'));
	}

	public function testRemoveAfter(): void
	{
		$this->assertSame('Mgr. Novák Jan', Degree::removeAfter('Mgr. Novák Jan Ph.D., LL.M.'));
		$this->assertSame('Jan Novák', Degree::removeAfter('Jan Novák Ph.D.'));
		$this->assertSame('Zůbek Ondřej', Degree::removeAfter('Zůbek Ondřej PhD. MBA'));
	}
}
