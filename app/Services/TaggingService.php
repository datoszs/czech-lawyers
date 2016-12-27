<?php
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

	/**
	 * Persist (but not flush) given entity if it is new case result tagging (i.e. when no such previous exists).
	 * @param TaggingCaseResult $entity
	 * @return bool
	 */
	public function persistCaseResultIfDiffers(TaggingCaseResult $entity)
	{
		$old = $this->orm->taggingCaseResults->getLastTagging($entity->case->id)->fetch();
		if ($this->isTaggingDifferent($entity, $old)) {
			$this->persist($entity);
			return true;
		}
		return false;
	}

	/**
	 * Returns true/false according to semantic difference of tagging.
	 * @param TaggingCaseResult $new New case tagging
	 * @param TaggingCaseResult|null $old Old case tagging (or null when no such tagging exists)
	 * @return bool
	 */
	private function isTaggingDifferent(TaggingCaseResult $new, $old)
	{
		return $old === null || !($old instanceof TaggingCaseResult) || $new->case != $old->case || $new->caseResult != $old->caseResult || $new->status != $old->status;
	}
}