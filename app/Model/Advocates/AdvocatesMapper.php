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
			->where('registration_number = %s OR id_advocate IN (SELECT advocate_id FROM advocate_info WHERE unaccent(concat_ws(\' \', degree_before, name, surname, degree_after)) ILIKE unaccent(%_like_) AND advocate_id = id_advocate)', $phrase, $phrase);
		if ($limit) {
			$builder->limitBy($limit, $start);
		} else {
			$builder->limitBy(null, $start);
		}
		return $builder;
	}

	/**
	 * Returns all advocates whose decile (in ranking) is same as given one.
	 * Ordered by score.
	 *
	 * @param int $decile Decile to be obtained, expected value 1-10.
	 * @param int $start Where to starts
	 * @param int $count Number of results
	 * @param bool $reverse Whether the results should be reversed
	 * @return QueryBuilder
	 */
	public function findFromDecile(int $decile, int $start, int $count, bool $reverse)
	{
		return $this
			->builder()
			->innerJoin('advocate', 'vm_advocate_score', 'vm_advocate_score', 'vm_advocate_score.id_advocate = advocate.id_advocate')
			->where('decile = %i', $decile)
			->limitBy($count, $start)
			->orderBy('score ' . ($reverse ? 'ASC' : 'DESC'));

	}
}
