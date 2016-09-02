<?php
namespace App\Model\Court;

use Nextras\Orm\Mapper\Mapper;

class CourtsMapper extends Mapper
{
	public function getTableName()
	{
		return 'court';
	}
}