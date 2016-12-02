<?php
namespace App\Model\Services;

use App\Model\Orm;


class nssDocumentService
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function findByDocumentId($documentId) {
        return $this
            ->orm
            ->documentsSupremeAdministrativeCourt
            ->getBy(['document' => $documentId]);
    }
}