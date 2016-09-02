<?php
namespace App\Model\Services;

use App\Model\Cause\Cause;
use App\Model\Orm;

class CauseService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function findOrCreate($registrySign)
	{
		$entity = $this->orm->causes->getBy(['registrySign' => $registrySign]);
		if (!$entity) {
			$entity = new Cause();
			$entity->registrySign = $registrySign;
			$this->orm->persist($entity);
		}
		return $entity;
	}

}