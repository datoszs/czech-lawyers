<?php
namespace App\Model\Services;


use App\Model\Documents\Document;
use App\Model\Documents\DocumentSupremeCourt;
use App\Model\Orm;

class DocumentService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function insert(Document $document, DocumentSupremeCourt $document2)
	{
		$this->orm->persistAndFlush($document);
		$this->orm->persistAndFlush($document2);
	}
}