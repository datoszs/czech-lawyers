<?php
namespace App\Model\Services;


use App\Model\Documents\Document;
use App\Model\Documents\DocumentSupremeCourt;
use App\Model\Orm;
use Nextras\Orm\Entity\Entity;

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
}