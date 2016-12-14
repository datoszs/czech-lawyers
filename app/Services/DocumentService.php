<?php
namespace App\Model\Services;


use App\Enums\Court as CourtEnum;
use App\Model\Documents\Document;
use App\Model\Orm;
use Nextras\Orm\Entity\IEntity;

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

	/**
	 * Returns extra data or null for given document
	 * @param Document $document
	 * @return IEntity|null
	 */
	public function findExtraData(Document $document)
	{
		if ($document->court->id == CourtEnum::TYPE_NSS) {
			return $this->orm->documentsSupremeAdministrativeCourt->getByDocumentId($document->id)->fetch();
		} elseif ($document->court->id == CourtEnum::TYPE_NS) {
			return $this->orm->documentsSupremeCourt->getByDocumentId($document->id)->fetch();
		} elseif ($document->court->id == CourtEnum::TYPE_US) {
			return $this->orm->documentsLawCourt->getByDocumentId($document->id)->fetch();
		}
		return null;
	}
}