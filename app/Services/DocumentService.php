<?php
namespace App\Model\Services;

use App\Model\Documents\Document;
use App\Model\Orm;
use Nextras\Dbal\Connection;
use Nextras\Orm\Collection\ICollection;
use App\Enums\Court as CourtEnum;
use Nextras\Orm\Entity\IEntity;

class DocumentService
{

	/** @var Orm */
	private $orm;

	/** @var Connection */
	private $connection;

	public function __construct(Orm $orm, Connection $connection)
	{
		$this->orm = $orm;
		$this->connection = $connection;
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

	public function save(IEntity $entity)
	{
		$this->orm->persist($entity);
	}

	public function get(int $documentId)
	{
		return $this->orm->documents->getById($documentId);
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

	public function findByCaseIdPairs($caseId)
	{
		return $this->orm
			->documents
			->findBy(['case' => $caseId])
			->orderBy('decisionDate',ICollection::DESC)
			->fetchPairs('id', 'recordId');
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
			return $this->orm->documentsConstitutionalCourt->getByDocumentId($document->id)->fetch();
		}
		return null;
	}

	public function findExtraByOrderNumber($orderNumber) {
		return $this->orm->documentsSupremeAdministrativeCourt->getBy(['orderNumber' => $orderNumber]);
	}

	public function findDocumentsWithoutFile () {
		return $this->connection->query("SELECT record_id FROM document WHERE court_id = 1 AND web_path = '' ORDER BY random() LIMIT 500")->fetchAll();
	}

	public function detach(Document $document): void
	{
		$this->orm->documents->detach($document);
	}
}
