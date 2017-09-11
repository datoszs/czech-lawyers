<?php declare(strict_types=1);
namespace App\Utils;


class Degree
{

	private static $beforeDegrees = [
		"prof.JUDr.",
		"JUDr.Ing.",
		"doc. JUDr.",
		"Mgr.Bc.",
		"Prof. Dr.",
		"JUDr. Mgr. Ing.",
		"Prof.Dr.",
		"JUDr. ICLic.",
		"Prom.práv.",
		"Mag.",
		"Dr.et Mgr.",
		"MILOSL.PROM.PRÁ",
		"doc.JUDr.",
		"Dr. Ing.",
		"Mgr. et Mgr.",
		"PhDr. Mgr.",
		"Prof. JUDr.",
		"JUDr. MUDr.",
		"Mgr., Ing.",
		"Doc. JUDr. Ing.",
		"Iic.iur",
		"Mag.Dr.",
		"Mgr. MgA.",
		"JUDr. Ing. et Ing.",
		"PhDr. Mgr. et Mgr.",
		"Doc.JUDr.Ing.",
		"JUDr.ing.",
		"Mgr.",
		"prom.práv.",
		"CHARLES. R.",
		"Mgr. Bc.",
		"Mgr. ICLic.",
		"Mag. iur.",
		"Ing.",
		"Ing. Mgr. Bc.",
		"Dr.",
		"doc. JUDr. Mgr.",
		"Dipl. práv.",
		"doc.",
		"doc. JUDr. Bc.",
		"JUDr.RSDr.",
		"Dr. iur.",
		"JUDr. Dr.",
		"JUDr. Mag.iur.",
		"Dr. iur. Mgr.",
		"Ass.jur.",
		"Mgr.MUDr.",
		"Bc. Mgr.",
		"Mgr. Mgr.",
		"JUDr.PhDr.",
		"Mgr. Dr.",
		"Dr. jur.",
		"JUDr. Mgr.",
		"prof. Dr.h.c. JUDr.",
		"Mag. Dr. Iur.",
		"Mgr. MUDr.",
		"JUDr.Mgr.",
		"Mgr. JUDr.",
		"Mgr.Ing.",
		"JUDr. PhDr.",
		"Dipl.-Jur.",
		"Mgr.et Mgr.",
		"Mgr. PhDr.",
		"Mgr., Mag.iur.",
		"prof. JUDr.",
		"Mgr. et. Mgr.",
		"JUDr. MgA.",
		"Judr.",
		"JUDr. ing.",
		"JUDr. RNDr.",
		"PhDr. JUDr.",
		"Mgr. Ing. arch.",
		"JUDr.",
		"JUDr. Dr. iur.",
		"PharmDr. JUDr.",
		"Dr. Iur.",
		"Mgr. PaedDr.",
		"JUDr. PhDr. Ing.",
		"Prof.JUDr.",
		"Ing. Mgr.",
		"Mgr.Dr.",
		"JUDr. Bc.",
		"prom.prav.",
		"Dipl.-Kfr",
		"Bc. Mgr. et Mgr.",
		"Doc.JUDr.",
		"JUDr. ThDr.",
		"Doc. Dr.",
		"JUDr. Ing. Bc.",
		"MgA. Mgr.",
		"Mgr., Bc.",
		"Prof., JUDr.",
		"Mag",
		"Dr.jur.",
		"Dr.iur.",
		"JUDr., PhDr.",
		"MMag.",
		"PhDr.Mgr.",
		"Doc. JUDr.",
		"MUDr. Mgr.",
		"JUDr. Ing.",
		"Mr. Sc.",
		"Mgr. Ing.",
	];

	private static $afterDegrees = [
		"CSc.",
		"Ph. D.",
		"PhD.",
		"BA",
		"Ph.D., LL.M.eur",
		"ST.",
		"Ing. CSc.",
		"LL.M., Ph.D.",
		"Ph.D., LLM",
		"Bc.",
		"LL.M., DBA",
		"MSc.",
		"PhDr.,CSc.",
		"ml.",
		"Ph.D, LL.M.",
		"LL.M. Eur. Int.",
		"J.D.,M.B.A",
		"BBA",
		"LL.M., LL.M., MSc.",
		"Ph.D., BA",
		"BA, Ph.D.",
		"Ph.D., LL.M",
		"DSc., MBA",
		"Jr.",
		"prom.práv.",
		"MBA, Ph.D.",
		"Esq.",
		"LL.M",
		"MBA",
		"Ph.D.,LL.M.",
		"Mgr. iur.",
		"Ing.,CSc.",
		"LL.M. Ph.D.",
		"Ing.",
		"MBA, LL.M.",
		"Ph.D., LL.M., MBA",
		"LL.M. Eur.",
		"Dr.",
		"LL.M.",
		"prom.prá.",
		"M.Jur.",
		"DiS.",
		"LL.M., M.B.A.",
		"MBA, LL.A.",
		"M.Jur.,Ph.D.",
		"LL.A.",
		"Lic.iur., Ph.D.",
		"Ph.D",
		"LL.M., Ph. D.",
		"LL.M.,LL.M.",
		"J.D., Ph.D.",
		"LL. M., MBA",
		"M.A.",
		"DrSc.",
		"Ph.D., LL.M. Eur.",
		"II.",
		"LL.M.,Ph.D",
		"PH.D.",
		"Ph.D., LL.M.",
		"B.A.",
		"LL.M.,Ph.D.",
		"LL.M., MBA",
		"PhDr.",
		"Ph.D., DSc.",
		"LL.M., LL.M.Eur., Ph.D.",
		"LL.M., MBA, Ph.D.",
		"Ph.D.",
		"LL.M.eur",
		"LL.B. (Hons.)",
		"Ph.D., JU.D.",
		"CSc., LL.M.",
		"PhD. MBA",
		"Ph.Dr.",
		"J.D.",
		"M. Jur.",
		"M.B.A.",
		"Ph.D., D.E.A.",
		"LL.M.Eur.,PhD.",
		"M.E.S.",
		"CSc., DSc.",
		"B.S.",
		"LL.M., MBA, MSc.",
		"B.A. LL.B.",
		"LL.B.",
		"Ph.D., MBA",
		"Ph.D., LL. M.",
	];

	/**
	 * Ensures that degrees before are sorted in descending order to prevent issue with Mgr. and Mgr. et. Mgr.
	 * @return array
	 */
	private static function getDegreesBefore()
	{
		static $sorted = false;
		if (!$sorted) {
			usort(static::$beforeDegrees, function ($a, $b) { return strnatcasecmp($a, $b) * -1; });
		}
		return static::$beforeDegrees;
	}

	/**
	 * Ensures that degrees after are sorted in descending order to prevent issue with Mgr. and Mgr. et. Mgr.
	 * @return array
	 */
	private static function getDegreesAfter()
	{
		static $sorted = false;
		if (!$sorted) {
			usort(static::$afterDegrees, function ($a, $b) { return strnatcasecmp($a, $b) * -1; });
		}
		return static::$afterDegrees;
	}

	/**
	 * Remove degree before from given name.
	 * @param string $string
	 * @return string
	 */
	public static function removeBefore(string $string): string
	{
		static $regex = null;
		if (!$regex) {
			$regex = '/^(' . implode('|', array_map(function ($value) { return preg_quote($value, '~'); }, static::getDegreesBefore())) .') /iu';
		}
		return preg_replace($regex, '', $string);
	}

	/**
	 * Remove degree after from given name.
	 * @param string $string
	 * @return string
	 */
	public static function removeAfter(string $string): string
	{
		static $regex = null;
		if (!$regex) {
			$regex = '/ (' . implode('|', array_map(function ($value) { return preg_quote($value, '~'); }, static::getDegreesAfter())) .')$/iu';
		}
		return preg_replace($regex, '', $string);
	}

	/**
	 * Returns degree from given advocate name
	 * @param string $string
	 * @return string
	 */
	public static function extractBefore(string $string): string
	{
		static $regex = null;
		if (!$regex) {
			$regex = '/^(' . implode('|', array_map(function ($value) { return preg_quote($value, '~'); }, static::getDegreesBefore())) .') /iu';
		}
		preg_match($regex, $string, $matches);
		if ($matches && $matches[1]) {
			return $matches[1];
		}
		return '';
	}

	/**
	 * Returns degree from given advocate name
	 * @param string $string
	 * @return string
	 */
	public static function extractAfter(string $string): string
	{
		static $regex = null;
		if (!$regex) {
			$regex = '/ (' . implode('|', array_map(function ($value) { return preg_quote($value, '~'); }, static::getDegreesAfter())) .')$/iu';
		}
		preg_match($regex, $string, $matches);
		if ($matches && $matches[1]) {
			return $matches[1];
		}
		return '';
	}
}
