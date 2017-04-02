<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Enums\AdvocateStatus;
use App\Enums\CaseResult;
use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Cause\Cause;
use App\Model\Documents\Document;
use App\Model\Services\AdvocateService;
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
	 *         "tagging_result": "negative",
	 *         "documents": [
	 *             {
	 *                 "id_document": 543,
	 *                 "mark": "ECLI:CZ:NS:2016:42.CDO.4000.2016.1",
	 *                 "decision_date": "2012-04-23T18:25:43.511Z",
	 *                 "public_link": "http://example.com/doc/12AS13LAA0"
	 *             }
	 *         ]
	 *     }
	 * </json>
	 *
	 * Potential tagging advocate:
	 *  - <b>array</b> when tagging present and valid
	 *  - <b>null</b> when null, or tagging is invalid
	 * Potential tagging result (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *  - <b>null</b> - when tagging is invalid
	 *
	 * @ApiRoute(
	 *     "/api/case/<id>",
	 *     parameters={
	 *         "id"={
	 *             "requirement": "\d+",
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
		$case = $this->causeService->get($id);
		if (!$case) {
			throw new BadRequestException("No such case [{$id}]", 404);
		}
		$documents = $this->documentService->findByCaseId($case->id);
		$results = $this->prepareCasesResults([$case]);
		$advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);


		// Transform to output
		$output = $this->mapDataToOutput($case, $documents, $results[$id] ?? null, $advocateTagging);
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

	private function mapDataToOutput(Cause $case, array $documents, ?TaggingCaseResult $result, ?TaggingAdvocate $taggingAdvocate)
	{
		/** @var AdvocateInfo $currentInfo */
		//$currentInfo = $advocate->advocateInfo->get()->fetch();
		$advocate = null;
		if ($taggingAdvocate && $taggingAdvocate->status === TaggingStatus::STATUS_PROCESSED) {
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
			'tagging_result' => ($result && $result->status === TaggingStatus::STATUS_PROCESSED) ? $result->caseResult : null,
			'documents' => array_map(function (Document $document) {
				return [
					'id_document' => $document->id,
					'mark' => $document->recordId,
					'decision_date' => $document->decisionDate->format(DateTime::ATOM),
					'public_link' => $document->webPath,
				];
			}, $documents)
		];
	}
}
