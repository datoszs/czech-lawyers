<?php
namespace App\Utils;



class TemplateFilters
{
	public static function common($filter, $value)
	{
		if (method_exists(__CLASS__, $filter)) {
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array([__CLASS__, $filter], $args);
		}
	}

	/**
	 * Formats given name according to czech grammar
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param null|string $degreeBefore
	 * @param null|string $degreeAfter
	 * @return string
	 */
	public static function formatName(string $firstname, string $lastname, ?string $degreeBefore = null, ?string $degreeAfter = null)
	{
		$output = [];
		$output[] = $degreeBefore;
		$output[] = $firstname;
		$output[] = $lastname;
		$temp = implode(' ', array_filter($output));
		if ($degreeAfter) {
			$temp .= ', ' . $degreeAfter;
		}
		return $temp;
	}
}
