<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class DocumentsMapper extends Mapper
{
	public function getTableName()
	{
		return 'document';
	}
}