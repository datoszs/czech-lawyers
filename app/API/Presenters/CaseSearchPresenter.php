<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Enums\TaggingStatus;
use App\Model\Cause\Cause;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\TemplateFilters;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for retrieving list of cases for search results
 *
 * @ApiRoute(
 *     "/api/case/search/",
 *     section="Cases",
 * )
 */
class CaseSearchPresenter extends Presenter
{

	/** @var CauseService @inject */
	public $caseService;

	/** @var TaggingService @inject */
	public $taggingService;

	/**
	 * Get relevant case
	 * Returns list of matched cases.
	 *
	 * <json>
	 *     [
	 *         {
	 *             "id_case": 234000,
	 *             "id_court": 3,
	 *             "registry_mark": "22 Cdo 2045/2012",
	 *             "tagging_result": "positive",
	 *             "tagging_result_final": true,
	 *         },
	 *     ]
	 * </json>
	 *
	 * There is one optional GET parameter:
	 *  - strategy - determines the matching strategy (from <b>start</b>, to <b>end</b> or anywhere in the <b>middle</b>).
	 *
	 * Note: provides only cases which are relevant for advocates portal.
	 *
	 * @ApiRoute(
	 *     "/api/case/search[/<query>/[<start>-<count>]]",
	 *     parameters={
	 *         "query"={
	 *             "requirement": ".+",
	 *             "type": "string",
	 *             "description": "Non empty string query to be matched anywhere in case registry mark.",
	 *         },
	 *         "start"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Specifies where to start.",
	 *             "default": 0
	 *         },
	 *         "count"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Specifies how many results to return. Maximum is 100.",
	 *             "default": 20
	 *         },
	 *     },
	 *     section="Cases",
	 *     presenter="API:CaseSearch",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param null|string $query Non empty string query to be matched anywhere in the advocate name or identification number.
	 * @param int $start Specifies where to start.
	 * @param int $count Specifies how many results to return. Maximum is 100.
	 * @throws AbortException when redirection happens
	 */
	public function actionRead(?string $query, int $start = 0, int $count = 20) : void
	{
		$strategy = in_array($this->getParameter('strategy'), ['start', 'end', 'middle'], true) ? $this->getParameter('strategy') : 'start';
		$count = min($count, 100); // enforce maximum
		$start = max ($start, 0); // enforce minimal start

		$output = [];
		if (!$query || Strings::length($query) === 0) {
			$this->sendJson($output);
			return;
		}
		// Load data
		$cases = $this->caseService->search($query, $start, $count, $strategy);
		$taggings = $this->prepareCasesResults($cases);
		$output = array_map(function (Cause $cause) use ($taggings) {
			return $this->mapCause($cause, $taggings[$cause->id] ?? null);
		}, $cases);
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

	private function mapCause(Cause $cause, ?TaggingCaseResult $result)
	{
		return [
			'id_case' => $cause->id,
			'id_court' => $cause->court->id,
			'registry_mark' => TemplateFilters::formatRegistryMark($cause->registrySign),
			'tagging_result' => ($result && $result->status === TaggingStatus::STATUS_PROCESSED) ? $result->caseResult : null,
			'tagging_result_final' => $result ? $result->isFinal : null,
		];
	}
}
