<?php
namespace App\Model\Documents;

use Nextras\Orm\Repository\Repository;

class DocumentsConstitutionalCourtRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [DocumentConstitutionalCourt::class];
	}

	public function getByDocumentId(int $documentId)
	{
		return $this->findBy(['document' => $documentId]);
	}
}
