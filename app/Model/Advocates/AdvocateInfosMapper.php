<?php
namespace App\Model\Advocates;

use Mikulas\OrmExt\MappingFactory;
use Nextras\Orm\Mapper\Mapper;

class AdvocateInfosMapper extends Mapper
{


    protected function createStorageReflection()
    {
        $factory = new MappingFactory(parent::createStorageReflection(), $this->getRepository()->getEntityMetadata());
        $factory->addStringArrayMapping('email');
        $factory->addStringArrayMapping('specialization');
        $factory->addCoordinatesMapping('location');

        return $factory->getStorageReflection();
    }

    public function getTableName()
    {
        return 'advocate_info';
    }

    public function findUniqueNames()
    {
        return $this->connection->query
        ("
        SELECT ai.*
        FROM
        (
            SELECT name, surname
            FROM advocate_info
            GROUP BY 1,2
            HAVING COUNT(DISTINCT(advocate_id))=1
        ) AS t
        INNER JOIN advocate_info AS ai ON (ai.name=t.name AND ai.surname=t.surname)
        ORDER BY t
        ");
    }
}
