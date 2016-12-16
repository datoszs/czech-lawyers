<?php
namespace App\Model\Documents;

use Nextras\Orm\Repository\Repository;

class DocumentsLawCourtRepository extends Repository
{

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [DocumentLawCourt::class];
	}

	public function getByDocumentId(int $documentId)
	{
		return $this->findBy(['document' => $documentId]);
	}
}