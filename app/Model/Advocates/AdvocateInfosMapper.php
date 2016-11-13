<?php
namespace App\Model\Advocates;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Orm\Mapper\Mapper;

class AdvocateInfosMapper extends Mapper
{


	protected function createStorageReflection()
	{
		$factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
		$factory->addStringArrayMapping('email');
		$factory->addStringArrayMapping('specialization');

		return $factory->getStorageReflection();
	}

	public function getTableName()
	{
		return 'advocate_info';
	}
}