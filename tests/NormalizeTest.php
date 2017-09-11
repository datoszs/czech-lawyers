<?php declare(strict_types=1);

use App\Utils\Normalize;
use PHPUnit\Framework\TestCase;


final class NormalizeTest extends TestCase
{

	public function testFixSurnameName() : void
	{
		$this->assertSame('', Normalize::fixSurnameName(''));
		$this->assertSame(' ', Normalize::fixSurnameName(' '));
		$this->assertSame('Jan Novák', Normalize::fixSurnameName('Novák Jan'));
		$this->assertSame('Lucie Nováková Štastná', Normalize::fixSurnameName('Nováková Štastná Lucie'));
		$this->assertSame('Lucie Nováková-Štastná', Normalize::fixSurnameName('Nováková-Štastná Lucie'));
		$this->assertSame('Mgr.et Mgr. Jan Novák Ph.D., LL.M.', Normalize::fixSurnameName('Mgr.et Mgr. Novák Jan Ph.D., LL.M.'));
		$this->assertSame('Mgr.et Mgr. Lucie Nováková Štastná Ph.D., LL.M.', Normalize::fixSurnameName('Mgr.et Mgr. Nováková Štastná Lucie Ph.D., LL.M.'));
		$this->assertSame('Mgr.et Mgr. Lucie Nováková-Štastná', Normalize::fixSurnameName('Mgr.et Mgr. Nováková-Štastná Lucie'));

		// s tituly
	}
}
