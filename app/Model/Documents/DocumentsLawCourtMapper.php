<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class DocumentsLawCourtMapper extends Mapper
{
	public function getTableName()
	{
		return 'document_law_court';
	}
}