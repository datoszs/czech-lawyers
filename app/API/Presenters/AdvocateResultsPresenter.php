<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Enums\Court;
use App\Model\Advocates\Advocate;
use App\Model\Services\AdvocateService;
use App\Model\Services\TaggingService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about advocate
 *
 * @ApiRoute(
 *     "/api/advocate-results/",
 *     section="Advocates",
 * )
 */
class AdvocateResultsPresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	/**
	 * Get information advocate statistics per year from given court (or all when court not specified).
	 *
	 * <json>
	 *     {
	 *         "id_advocate": 123,
	 *         "id_court": 2,
	 *         "years" : {
	 *             2014: {
	 *                 "negative": 12,
	 *                 "neutral": 2,
	 *                 "positive": 59,
	 *             },
	 *             2016: {
	 *                 "negative": 10,
	 *                 "neutral": 0,
	 *                 "positive": 5,
	 *             },
	 *         }
	 *     }
	 * </json>
	 *
	 * For statistics for all courts the <b>id_court</b> field is null.
	 * Each year has its key, however if there are no data for year then the year is ommited.
	 *
	 * @ApiRoute(
	 *     "/api/advocate-results/<advocate>[/<court>]",
	 *     parameters={
	 *         "advocate"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Advocate ID.",
	 *         },
	 *         "court"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Court ID.",
	 *         },
	 *     },
	 *     section="Advocates",
	 *     presenter="API:AdvocateResults",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param int $advocate Advocate ID
	 * @param int|null $court
	 * @throws BadRequestException when advocate was not found
	 * @internal param $ ?int $court Court ID
	 */
	public function actionRead(int $advocate, ?int $court = null) : void
	{
		if ($court && !in_array($court, Court::$types, true)) {
			throw new BadRequestException("No such acourt [{$court}]", 404);
		}
		// Load data
		$advocateEntity = $this->advocateService->get($advocate);
		if (!$advocateEntity) {
			throw new BadRequestException("No such advocate [{$advocate}]", 404);
		}
		$statistics = $this->taggingService->computeAdvocateStatisticsPerYear($advocate, $court);
		// Transform to output
		$output = $this->mapAdvocateStatistics($advocateEntity, $court, $statistics);
		// Send output
		$this->sendJson($output);
	}

	private function mapAdvocateStatistics(Advocate $advocate, ?int $court, array $statistics)
	{
		return [
			'id_advocate' => $advocate->id,
			'id_court' => $court,
			'years' => $statistics
		];
	}
}
