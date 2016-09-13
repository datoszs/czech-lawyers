<?php
namespace App\Model\Services;

use App\Enums\Court;
use App\Model\Orm;

class CourtService
{

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function getById($court)
	{
		return $this->orm->courts->getById($court);
	}

	public function getNS()
	{
		return $this->orm->courts->getById(Court::TYPE_NS);
	}

	public function getNSS()
	{
		return $this->orm->courts->getById(Court::TYPE_NSS);
	}

	public function getUS()
	{
		return $this->orm->courts->getById(Court::TYPE_US);
	}

}