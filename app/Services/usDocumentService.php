<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 30.11.2016
 * Time: 14:54
 */

namespace App\Model\Services;

use App\Model\Orm;

class usDocumentService
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
            ->documentsLawCourt
            ->getBy(['document' => $documentId]);
    }
}