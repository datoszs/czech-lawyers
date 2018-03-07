<?php
namespace App\Model\Documents;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use DateTime;
use Nextras\Orm\Entity\Entity;

/**
 * @property int				$id					{primary}
 * @property Document			$document			{m:1 Document, oneSided=true}
 * @property string				$ecli
 * @property string				$formDecision
 * @property string|null		$decisionResult
 * @property string|null		$paralelReferenceLaws
 * @property string|null		$paralelReferenceJudgements
 * @property string|null		$popularTitle
 * @property DateTime|null		$decisionDate
 * @property DateTime|null		$deliveryDate
 * @property DateTime|null		$filingDate
 * @property DateTime|null		$publicationDate
 * @property string|null		$proceedingsType
 * @property int				$importance
 * @property array|null			$proposer
 * @property array|null			$institutionConcerned
 * @property string|null		$justiceRapporteur
 * @property array|null			$contestedAct
 * @property array|null			$concernedLaws
 * @property array|null			$concernedOther
 * @property array|null			$dissentingOpinion
 * @property array|null			$proceedingsSubject
 * @property array|null			$subjectIndex
 * @property string|null		$rulingLanguage
 * @property string|null		$note
 * @property array|null			$names
 */
class DocumentConstitutionalCourt extends Entity
{

}
