<?php

namespace Mikulas\OrmExt;

use Mikulas\OrmExt\Pg\PgArray;
use Nextras\Orm\Entity\Reflection\EntityMetadata;
use Nextras\Orm\InvalidArgumentException;
use Nextras\Orm\Mapper\Dbal\StorageReflection\IStorageReflection;
use Nextras\Orm\Mapper\Dbal\StorageReflection\StorageReflection;


class MappingFactory
{

	/** @var StorageReflection */
	private $storageReflection;

	/** @var EntityMetadata */
	private $entityMetadata;


	public function __construct(IStorageReflection $storageReflection, EntityMetadata $entityMetadata)
	{
		$this->storageReflection = $storageReflection;
		$this->entityMetadata = $entityMetadata;
	}


	/**
	 * @param string   $propertyName
	 * @param callable $toEntityTransform
	 * @param callable $toSqlTransform
	 * @throws InvalidPropertyException
	 */
	public function addGenericArrayMapping($propertyName, callable $toEntityTransform, callable $toSqlTransform)
	{
		$this->validateProperty($propertyName);

		$this->storageReflection->setMapping(
			$propertyName,
			$this->storageReflection->convertEntityToStorageKey($propertyName),
			function ($value) use ($toEntityTransform) {
				return PgArray::parse($value, $toEntityTransform);
			},
			function ($value) use ($toSqlTransform) {
				return PgArray::serialize($value, $toSqlTransform);
			}
		);
	}


	/**
	 * @param string $propertyName
	 * @throws InvalidPropertyException
	 */
	public function addStringArrayMapping($propertyName)
	{
		$toEntity = function($partial) {
			return (string) $partial;
		};
		$toSql = function($partial) {
			return '"' . $partial . '"';
		};

		$this->addGenericArrayMapping($propertyName, $toEntity, $toSql);
	}


	/**
	 * @param string $propertyName
	 * @throws InvalidPropertyException
	 */
	public function addIntArrayMapping($propertyName)
	{
		$toEntity = function($partial) {
			return (int) $partial;
		};
		$toSql = function($partial) {
			return $partial;
		};

		$this->addGenericArrayMapping($propertyName, $toEntity, $toSql);
	}


	/**
	 * Expects normalized dates without timezones
	 * @param string $propertyName
	 * @throws InvalidPropertyException
	 */
	public function addDateTimeArrayMapping($propertyName)
	{
		$toEntity = function($partial) {
			return new \DateTime($partial);
		};
		$toSql = function(\DateTimeInterface $partial) {
			return '"' . $partial->format('Y-m-d H:i:s') . '"';
		};

		$this->addGenericArrayMapping($propertyName, $toEntity, $toSql);
	}


	/**
	 * @param string $propertyName
	 * @throws InvalidPropertyException
	 */
	public function validateProperty($propertyName)
	{
		try {
			$this->entityMetadata->getProperty($propertyName);
		} catch (InvalidArgumentException $e) {
			throw InvalidPropertyException::createNonexistentProperty($propertyName, $e);
		}
	}


	/**
	 * @return StorageReflection
	 */
	public function getStorageReflection()
	{
		return $this->storageReflection;
	}

}