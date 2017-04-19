<?php
namespace App\Model\Documents;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Orm\Mapper\Mapper;

class DocumentsSupremeAdministrativeCourtMapper extends Mapper
{
	protected function createStorageReflection()
	{
		$factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
		$factory->addJsonMapping('sides');
		$factory->addJsonMapping('prejudicate');

		return $factory->getStorageReflection();
	}

	public function getTableName()
	{
		return 'document_supreme_administrative_court';
	}
}
