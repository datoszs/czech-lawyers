<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class AdvocatesMapper extends Mapper
{
	public function getTableName()
	{
		return 'advocate';
	}
}