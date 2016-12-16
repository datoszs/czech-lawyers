<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 29.11.2016
 * Time: 14:07
 */

namespace App\Model\Services;

use App\Model\Taggings\TaggingCaseResult;
use App\Model\Orm;
use Nextras\Orm\Entity\IEntity;


class TaggingService
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function insert($result)
    {
        $this->orm->persist($result);
    }

    public function persist(IEntity $entity)
    {
        $this->orm->persist($entity);
    }

    public function flush()
    {
        $this->orm->flush();
    }

    public function findByDocument(IEntity $document)
    {
        return $this
            ->orm
            ->taggingCaseResults
            ->getBy(['document' => $document]);
    }
}