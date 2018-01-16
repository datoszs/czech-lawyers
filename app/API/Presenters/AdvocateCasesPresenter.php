<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use App\Enums\CaseResult;
use App\Enums\Court;
use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Services\AdvocateService;
use App\Model\Services\AnnulmentService;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
use App\Utils\TemplateFilters;
use DateTime;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about advocate
 *
 * @ApiRoute(
 *     "/api/advocate-cases/",
 *     section="Advocates",
 * )
 */
class AdvocateCasesPresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var CauseService @inject */
	public $causeService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var ILogger @inject */
	public $auditing;

	/** @var AnnulmentService @inject */
	public $annulmentService;

	/**
	 * Get advocate cases fulfilling given filters (or all when no filters given)
	 *
	 * <json>
	 *     {
	 *         "id_advocate": 123,
	 *         "id_court": 2,
	 *         "year": 2016
	 *         "result":
	 *         "cases" : [
	 *             {
	 *                 "id_case": 25,
	 *                 "id_court": 2,
	 *                 "registry_mark": "42 CDO 4000/2016",
	 *                 "result": "negative",
	 *                 "proposition_date": "2016-03-01T01:00:00+01:00",
	 *                 "decision_date": null,
	 *                 "annuled": true,
	 *                 "annuled_by_id_cases": [2, null],
	 *             }
	 *         ]
	 *     }
	 * </json>
	 *
	 * Through query object additional filters can be provided:
	 *  - <b>id_court</b> (e.g. 2)
	 *  - <b>year</b> (e.g. 2016)
	 *  - <b>result</b> (e.g. negative)
	 *
	 * Available results (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *  - <b><i>null</i></b> when no result available
	 *
	 * Annuling of cases:
	 *  - When this case is annuled then <b>annuled</b> is true, otherwise false.
	 *  - When this case is annuled then <b>annuled_by_id_cases</b> contains array with ids of cases which annuled this case (or nulls when we don't have this information)
	 *
	 * When additional filter is provided its value is returned in response.
	 *
	 * Note: provides only cases which are relevant for advocates portal.
	 *
	 * Errors:
	 *  - Returns HTTP 404 with error <b>no_advocate</b> when such advocate doesn't exist
	 *  - Returns HTTP 400 with error <b>invalid_court</b> when given court is invalid
	 *  - Returns HTTP 400 with error <b>invalid_result</b> when given case result type is invalid
	 *
	 * @ApiRoute(
	 *     "/api/advocate-cases/<advocate>",
	 *     parameters={
	 *         "advocate"={
	 *             "requirement": "-?\d+",
	 *             "type": "integer",
	 *             "description": "Advocate ID.",
	 *         },
	 *     },
	 *     section="Advocates",
	 *     presenter="API:AdvocateCases",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param int $advocate Advocate ID
	 * @throws BadRequestException when advocate was not found
	 * @throws AbortException when redirecting/forwarding
	 */
	public function actionRead(int $advocate) : void
	{
		// Obtain parameters
		$court = $this->getRequest()->getParameter('court') ?? null;
		$year = $this->getRequest()->getParameter('year') ?? null;
		$result = $this->getRequest()->getParameter('result') ?? null;

		// Process and validate parameters
		if ($court) {
			$court = (int) $court;
			if (!in_array($court, Court::$types, true)) {
				$this->getHttpResponse()->setCode(400);
				$this->sendJson(['error' => 'invalid_court', 'message' => "No such court [{$court}]"]);
				return;
			}
		}
		if ($year) {
			$year = (int) $year;
		}
		if ($result && !isset(CaseResult::$statuses[$result])) {
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'invalid_result', 'message' => "No such result [{$result}]"]);
			return;
		}
		// Load data
		$advocateEntity = $this->advocateService->get($advocate);
		if (!$advocateEntity) {
			$this->getHttpResponse()->setCode(404);
			$this->sendJson(['error' => 'no_advocate', 'message' => "No such advocate [{$advocate}]"]);
			return;
		}
		$cases = $this->causeService->findFromAdvocate($advocate, $court, $year, $result);
		$results = $this->prepareCasesResults($cases->fetchAll());
		$annulments = $this->annulmentService->findComputedAnnulmentOfCases($cases->fetchAll());
		// Transform to output
		$output = $this->mapAdvocateCases($advocateEntity, $cases, $results, $court, $year, $result, $annulments);
		// Auditing
		$advocateTaggings = $this->prepareAdvocateTaggings($cases->fetchAll());
		/** @var Cause $case */
		$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocateEntity->getCurrentName()}] with ID [{$advocateEntity->id}].", AuditedReason::REQUESTED_INDIVIDUAL);
		foreach ($cases as $case) {
			$caseResultCaseResult = isset($results[$case->id]) ? $results[$case->id]->caseResult : null;
			$caseResultStatus = isset($results[$case->id]) ? $results[$case->id]->status : null;
			$caseResultId = isset($results[$case->id]) ? $results[$case->id]->id : null;
			$advocateTaggingId = isset($advocateTaggings[$case->id]) ? $advocateTaggings[$case->id]->id : null;
			$this->auditing->logAccess(AuditedSubject::CASE_TAGGING, "Load advocate tagging with ID [{$advocateTaggingId}] of case [{$case->registrySign}] and advocate [{$advocateEntity->getCurrentName()}] with ID [{$advocateEntity->id}] together with result [{$caseResultCaseResult} - {$caseResultStatus}] with ID [{$caseResultId}].", AuditedReason::REQUESTED_BATCH);
		}
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

	private function prepareAdvocateTaggings(array $causes): array
	{
		$output = [];
		$temp = $this->taggingService->findLatestAdvocateTaggingByCases($causes);
		foreach ($temp as $row) {
			$output[$row->case->id] = $row;
		}
		return $output;
	}

	private function mapAdvocateCases(Advocate $advocate, ICollection $cases, array $results, ?int $court, ?int $year, ?string $result, array $annulments)
	{
		$output = [
			'id_advocate' => $advocate->id,
		];
		if ($court) {
			$output['id_court'] = $court;
		}
		if ($year) {
			$output['year'] = $year;
		}
		if ($result) {
			$output['result'] = $result;
		}
		$output['cases'] = [];
		/** @var Cause $case */
		foreach ($cases as $case)
		{
			$output['cases'][] = [
				'id_case' => $case->id,
				'id_court' => $case->court->id,
				'registry_mark' => TemplateFilters::formatRegistryMark($case->registrySign),
				'result' => isset($results[$case->id]) ? $results[$case->id]->caseResult : null,
				'decision_date' => $case->decisionDate ? $case->decisionDate->format(DateTime::ATOM) : null,
				'proposition_date' => $case->propositionDate ? $case->propositionDate->format(DateTime::ATOM) : null,
				'annuled' => count($annulments) > 0 ? true : false,
				'annuled_by_id_cases' => $annulments[$case->id] ?? [],
			];
		}
		return $output;
	}
}
