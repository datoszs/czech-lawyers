<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class DocumentsSupremeCourtMapper extends Mapper
{
	public function getTableName()
	{
		return 'document_supreme_court';
	}
}