<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use App\Enums\AdvocateStatus;
use App\Enums\CaseResult;
use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Annulments\Annulment;
use App\Model\Cause\Cause;
use App\Model\Documents\Document;
use App\Model\Services\AdvocateService;
use App\Model\Services\AnnulmentService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\TemplateFilters;
use DateTime;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about case
 *
 * @ApiRoute(
 *     "/api/case/",
 *     section="Cases",
 * )
 */
class CasePresenter extends Presenter
{

	/** @var CauseService @inject */
	public $causeService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var AnnulmentService @inject */
	public $annulmentService;

	/** @var ILogger @inject */
	public $auditing;

	/**
	 * Get information about case with given ID.
	 *
	 * <json>
	 *     {
	 *         "id_case": 12,
	 *         "id_court": 2,
	 *         "registry_mark": "42 CDO 4000/2016",
	 *         "tagging_advocate": {
	 *             "id_advocate": 123,
	 *             "fullname": "JUDr. Ing. Petr Omáčka, PhD."
	 *         },
	 *         "tagging_advocate_final": true,
	 *         "tagging_result": "negative",
	 *         "tagging_result_final": false,
	 *         "proposition_date": "2016-03-01T01:00:00+01:00",
	 *         "decision_date": null,
	 *         "documents": [
	 *             {
	 *                 "id_document": 543,
	 *                 "mark": "ECLI:CZ:NS:2016:42.CDO.4000.2016.1",
	 *                 "decision_date": "2012-04-23T18:25:43.511Z",
	 *                 "public_link": "http://example.com/doc/12AS13LAA0"
	 *                 "public_local_link": "http://example.com/doc/12AS13LAA0"
	 *             }
	 *         ],
	 *         "tagging_result_annuled": true,
	 *         "tagging_result_annuled_by_id_cases": [2, null],
	 *         "tagging_result_annuling_id_cases": [],
	 *     }
	 * </json>
	 *
	 * Potential tagging advocate:
	 *  - <b>array</b> when tagging present and valid
	 *  - <b>null</b> when null, or tagging is invalid
	 *
	 * Potential tagging result (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *  - <b>null</b> - when tagging is invalid
	 *
	 * Potential tagging final result:
	 *  - <b>null</b> when tagging doesn't exist
	 *  - <b>true</b> when tagging is final
	 *  - <b>false</b> when tagging is not final
	 *
	 * Annuling of cases:
	 *  - When this case is annuled then <b>tagging_result_annuled</b> is true, otherwise false.
	 *  - When this case is annuled then <b>tagging_result_annuled_by_id_cases</b> contains array with ids of cases which annuled this case (or nulls when we don't have this information)
	 *  - When this case is annuling any case then <b>tagging_result_annuling_id_cases</b> contains ids of annuled cases
	 *
	 * Note: provides only cases which are relevant for advocates portal.
	 *
	 * Errors:
	 *  - Returns HTTP 404 with error <b>no_case</b> when such case doesn't exist
	 *
	 * @ApiRoute(
	 *     "/api/case/<id>",
	 *     parameters={
	 *         "id"={
	 *             "requirement": "-?\d+",
	 *             "type": "integer",
	 *             "description": "Case ID.",
	 *         },
	 *     },
	 *     section="Cases",
	 *     presenter="API:Case",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param int $id Case ID
	 * @throws AbortException when redirection happens
	 * @throws BadRequestException when case not found
	 */
	public function actionRead(int $id) : void
	{
		// Load data
		$case = $this->causeService->getRelevantForAdvocates($id);
		if (!$case) {
			$this->getHttpResponse()->setCode(404);
			$this->sendJson(['error' => 'no_case', 'message' => "No such case [{$id}]"]);
			return;
		}
		$documents = $this->documentService->findByCaseId($case->id);
		$results = $this->prepareCasesResults([$case]);
		$advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);

		/* @var Annulment $annulment */
		$annulments = $this->annulmentService->findComputedAnnulmentOfCases([$case]);
		$annuling = $this->annulmentService->findComputedAnnulingOfCases([$case]);

		// Transform to output
		$output = $this->mapDataToOutput($case, $documents, $results[$id] ?? null, $advocateTagging, $annulments, $annuling);
		// Auditing
		$advocateTaggingId = $advocateTagging ? $advocateTagging->id : null;
		$advocateId = $advocateTagging && $advocateTagging->advocate ? $advocateTagging->advocate->id : null;
		$advocateName = $advocateTagging && $advocateTagging->advocate ? $advocateTagging->advocate->getCurrentName() : null;
		$caseResultCaseResult = isset($results[$case->id]) ? $results[$case->id]->caseResult : null;
		$caseResultStatus = isset($results[$case->id]) ? $results[$case->id]->status : null;
		$caseResultId = isset($results[$case->id]) ? $results[$case->id]->id : null;
		$this->auditing->logAccess(AuditedSubject::CASE_TAGGING, "Load advocate tagging with ID [{$advocateTaggingId}] of case [{$case->registrySign}] and advocate [{$advocateName}] with ID [{$advocateId}] together with result [{$caseResultCaseResult} - {$caseResultStatus}] with ID [{$caseResultId}].", AuditedReason::REQUESTED_INDIVIDUAL);
		// Send output
		$this->sendJson($output);
	}

	private function prepareCasesResults($data)
	{
		$output = [];
		$temp = $this->taggingService->findCaseResultLatestTaggingByCases($data);
		foreach ($temp as $row) {
			$output[$row->case->id] = $row;
		}
		return $output;
	}

	private function mapDataToOutput(Cause $case, array $documents, ?TaggingCaseResult $result, ?TaggingAdvocate $taggingAdvocate, array $annulments, array $annuling)
	{
		/** @var AdvocateInfo $currentInfo */
		$advocate = null;
		if ($taggingAdvocate && $taggingAdvocate->status === TaggingStatus::STATUS_PROCESSED && $taggingAdvocate->advocate && $taggingAdvocate->advocate->advocateInfo) {
			$currentInfo = $taggingAdvocate->advocate->advocateInfo->get()->fetch();
			$advocate = [
				'id_advocate' => $taggingAdvocate->advocate->id,
				'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter)
			];
		}
		return [
			'id_case' => $case->id,
			'id_court' => $case->court->id,
			'registry_mark' => TemplateFilters::formatRegistryMark($case->registrySign),
			'tagging_advocate' => $advocate,
			'tagging_advocate_final' => $taggingAdvocate ? $taggingAdvocate->isFinal : null,
			'tagging_result' => ($result && $result->status === TaggingStatus::STATUS_PROCESSED) ? $result->caseResult : null,
			'tagging_result_final' => $result ? $result->isFinal : null,
			'decision_date' => $case->decisionDate ? $case->decisionDate->format(DateTime::ATOM) : null,
			'proposition_date' => $case->propositionDate ? $case->propositionDate->format(DateTime::ATOM) : null,
			'documents' => array_map(function (Document $document) {
				return [
					'id_document' => $document->id,
					'mark' => $document->recordId,
					'decision_date' => $document->decisionDate->format(DateTime::ATOM),
					'public_link' => $document->webPath,
					'public_local_link' => $this->link('//:Document:view', $document->id),
				];
			}, $documents),
			'tagging_result_annuled' => isset($annulments[$case->id]) && count($annulments[$case->id]) > 0 ? true : false,
			'tagging_result_annuled_by_id_cases' => $annulments[$case->id] ?? [],
			'tagging_result_annuling_id_cases' => $annuling[$case->id] ?? []
		];
	}
}
