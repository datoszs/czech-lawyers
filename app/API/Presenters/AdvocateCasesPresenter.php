<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Enums\CaseResult;
use App\Enums\Court;
use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Services\AdvocateService;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
use App\Utils\TemplateFilters;
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
	 * When additional filter is provided its value is returned in response.
	 *
	 * Note: provides only cases which are relevant for advocates portal.
	 *
	 * @ApiRoute(
	 *     "/api/advocate-cases/<advocate>",
	 *     parameters={
	 *         "advocate"={
	 *             "requirement": "\d+",
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
				throw new BadRequestException("No such court [{$court}]", 404);
			}
		}
		if ($year) {
			$year = (int) $year;
		}
		if ($result && !isset(CaseResult::$statuses[$result])) {
			throw new BadRequestException("No such result [{$result}]", 404);
		}
		// Load data
		$advocateEntity = $this->advocateService->get($advocate);
		if (!$advocateEntity) {
			throw new BadRequestException("No such advocate [{$advocate}]", 404);
		}
		$cases = $this->causeService->findFromAdvocate($advocate, $court, $year, $result);
		$results = $this->prepareCasesResults($cases->fetchAll());
		// Transform to output
		$output = $this->mapAdvocateCases($advocateEntity, $cases, $results, $court, $year, $result);
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

	private function mapAdvocateCases(Advocate $advocate, ICollection $cases, array $results, ?int $court, ?int $year, ?string $result)
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
			];
		}
		return $output;
	}
}
