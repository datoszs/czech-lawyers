<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Model\Services\TaggingService;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about courts
 *
 * @ApiRoute(
 *     "/api/court-statistics/",
 *     section="Feedback",
 * )
 */
class CourtStatisticsPresenter extends Presenter
{

	/** @var TaggingService @inject */
	public $taggingService;

	/**
	 * Get statistics of each tagged case results type (@see CaseResult) grouped by court.
	 *
	 * <json>
	 *     {
	 *         "court_statistics": {
	 *             "1": {
	 *                 "negative": 11,
	 *                 "neutral": 1,
	 *                 "positive": 57,
	 *             },
	 *             "2": {
	 *                 "negative": 1,
	 *                 "neutral": 1,
	 *                 "positive": 2,
	 *             },
	 *             "3": {
	 *                 "negative": 12,
	 *                 "neutral": 13,
	 *                 "positive": 21,
	 *             }
	 *         }
	 *     }
	 * </json>
	 *
	 * Available statistics results (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *
	 * Note: statistics take into account only cases which are relevant for advocates portal, which are tagged with result AND with advocate
	 *
	 * @ApiRoute(
	 *     "/api/court-statistics",
	 *     parameters={},
	 *     section="Courts",
	 *     presenter="API:CourtStatistics",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws AbortException when redirecting/forwarding
	 */
	public function actionRead() : void
	{
		// Load data
		$statistics = $this->taggingService->computeCourtStatisticsPerCourt();
		// Transform to output
		// Send output
		$this->sendJson($statistics);
	}
}
