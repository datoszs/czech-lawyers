<?php
namespace App\Model\Cause;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Orm\Mapper\Mapper;

class CausesMapper extends Mapper
{
	protected function createStorageReflection()
	{
		$factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
		$factory->addJsonMapping('officialData');

		return $factory->getStorageReflection();
	}

	public function getTableName()
	{
		return 'case';
	}
}