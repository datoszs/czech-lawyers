<?php
namespace App\Model\Advocates;

use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Mapper\Mapper;

class AdvocatesMapper extends Mapper
{
	public function getTableName()
	{
		return 'advocate';
	}

	/**
	 * Search and returns all advocates fulfilling search condition (mathing name or registration number - IČ).
	 * Note: case insensitive search
	 *
	 * @param string $phrase Phrase to be searched
	 * @param int $start Offset of first result.
	 * @param int|null $limit Maximal number of results, more than some threashold will result in memory limit problems
	 * @return QueryBuilder
	 */
	public function search(string $phrase, int $start = 0, ?int $limit = null)
	{
		$builder = $this
			->builder()
			->where('registration_number = %s OR id_advocate IN (SELECT advocate_id FROM advocate_info WHERE concat_ws(\' \', degree_before, name, surname, degree_after) ILIKE %_like_ AND advocate_id = id_advocate)', $phrase, $phrase);
		if ($limit) {
			$builder->limitBy($limit, $start);
		} else {
			$builder->limitBy(null, $start);
		}
		return $builder;
	}
}
