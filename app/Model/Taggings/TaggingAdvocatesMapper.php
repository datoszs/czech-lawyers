<?php
namespace App\Model\Documents;

use Nextras\Orm\Mapper\Mapper;

class TaggingAdvocatesMapper extends Mapper
{
	public function getTableName()
	{
		return 'tagging_advocate';
	}
}