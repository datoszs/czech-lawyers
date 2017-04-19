<?php
namespace App\Model\Documents;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Orm\Mapper\Mapper;

class DocumentsLawCourtMapper extends Mapper
{
	protected function createStorageReflection()
	{
		$factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
		$factory->addJsonMapping('proposer');
		$factory->addJsonMapping('institutionConcerned');
		$factory->addJsonMapping('contestedAct');
		$factory->addJsonMapping('concernedLaws');
		$factory->addJsonMapping('concernedOther');
		$factory->addJsonMapping('dissentingOpinion');
		$factory->addJsonMapping('proceedingsSubject');
		$factory->addJsonMapping('subjectIndex');
		$factory->addJsonMapping('names');

		return $factory->getStorageReflection();
	}

	public function getTableName()
	{
		return 'document_law_court';
	}
}
