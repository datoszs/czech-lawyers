<?php
namespace App\Model\Annulments;


use Nextras\Dbal\Result\Result;
use Nextras\Orm\Mapper\Mapper;

class AnnulmentMapper extends Mapper
{

	public function getTableName()
	{
		return 'case_annulment';
	}

	/**
	 * Returns case annulment of given cases
	 *
	 * @param int[] $causesIds
	 * @return Result|NULL
	 */
	public function findAnnulmentsByCases(array $causesIds)
	{
		return $this->connection->query('
			SELECT * FROM vw_computed_case_annulment WHERE annuled_case IN %i[]
		', $causesIds);
	}
}
