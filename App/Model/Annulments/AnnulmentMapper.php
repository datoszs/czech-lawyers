<?php
namespace App\Model\Annulments;


use Nextras\Orm\Mapper\Mapper;

class AnnulmentMapper extends Mapper
{

	public function getTableName()
	{
		return 'case_annulment';
	}
}
