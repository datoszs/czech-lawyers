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

	public function testRegistryMark() : void
	{
		// lowerization
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("27 CDO 5738/2016"));
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("27 Cdo 5738/2016"));
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("27 cDO 5738/2016"));
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("27 cdo 5738/2016"));

		// test space unification
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark(" 27  cdo  5738/2016  "));
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("	27	cdo	5738/2016	"));
		$this->assertSame("27 cdo 5738/2016", Normalize::registryMark("	27	cdo	5738/2016	"));

		// weird cases of ÚS
		$this->assertSame("i. ús 1549/11", Normalize::registryMark("1. ús 1549/11"));
		$this->assertSame("i. ús 1549/11", Normalize::registryMark("1.ús 1549/11"));
		$this->assertSame("i. ús 1549/11", Normalize::registryMark("1 ús 1549/11"));
		$this->assertSame("i. ús-st. 1549/11", Normalize::registryMark("1.ús-st. 1549/11"));

		// weird cases of NS
		$this->assertSame("21 nscr 230/2015", Normalize::registryMark("21 NSČR 230/2015"));
		$this->assertSame("21 nscr 230/2015", Normalize::registryMark("21 NSCR 230/2015"));
		$this->assertSame("21 nscr 230/2015", Normalize::registryMark("21 nsčr 230/2015"));
		$this->assertSame("21 nscr 230/2015", Normalize::registryMark("21 nscr 230/2015"));

	}
}
