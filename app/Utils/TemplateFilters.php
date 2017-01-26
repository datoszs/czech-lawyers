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

	public static function formatName($firstname, $lastname, $degreeBefore = null, $degreeAfter = null)
	{
		$output = [];
		$output[] = $degreeBefore;
		$output[] = $firstname;
		$output[] = $lastname;
		$output[] = $degreeAfter;
		return implode(' ', array_filter($output));
	}
}
