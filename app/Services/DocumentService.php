<?php
namespace App\Model\Services;


use App\Enums\Court;
use App\Model\Documents\Document;
use App\Model\Orm;
use Nextras\Orm\Collection\ICollection;

class DocumentService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function insert(Document $document, $document2 = null, $flush = false)
	{
		$this->orm->persist($document);
		if ($document2) {
			$this->orm->persist($document2);
		}
		if ($flush) {
			$this->orm->flush();
		}
	}
	public function flush()
	{
		$this->orm->flush();
	}

	public function findByRecordId($recordId)
	{
		return $this->orm->documents->findBy(['recordId' => $recordId])->fetch();
	}

	public function findByCaseId($caseId)
    {
        return $this->orm
            ->documents
            ->findBy(['case' => $caseId])
            ->orderBy('decisionDate',ICollection::DESC)
            ->fetchAll();
    }

    public function findExtra($courtId, $documentId) {
	    if (Court::TYPE_NSS == $courtId)
        {
            return $this->orm
                ->documentsSupremeAdministrativeCourt
                ->findBy(['document' => $documentId])
                ->fetch();
        } elseif (Court::TYPE_US == $courtId)
        {
            return $this->orm
                ->documentsLawCourt
                ->findBy(['document' => $documentId])
                ->fetch();
	    } elseif (Court::TYPE_NS == $courtId)
        {
            return $this->orm
                ->documentsSupremeCourt
                ->findBy(['document' => $documentId])
                ->fetch();
	    }else
          return NULL;
	}
}