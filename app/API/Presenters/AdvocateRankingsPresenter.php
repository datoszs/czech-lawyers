<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Services\AdvocateService;
use App\Utils\TemplateFilters;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about advocate rankings
 *
 * @ApiRoute(
 *     "/api/advocate-rankings/",
 *     section="Advocates",
 * )
 */
class AdvocateRankingsPresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/**
	 * Get advocates which are in given decile in advocate rankings.
	 *
	 * <json>
	 *     {
	 *         [
	 *             "id_advocate": 123,
	 *             "fullname": "JUDr. Ing. Petr Omáčka, PhD.",
	 *             "sorting_name": "Omáčka, Petr",
	 *         ],
	 *         [
	 *             "id_advocate": 1118,
	 *             "fullname": "JUDr. Stanislav Morče",
	 *             "sorting_name": "Morče, Stanislav",
	 *         ]
	 *     }
	 * </json>
	 *
	 * There is one optional GET parameter:
	 *  - <b>reverse</n> - presence indicates reverse sorting (usable for last people from given decile)
	 *
	 * Errors:
	 *  - Returns HTTP 400 with error <b>invalid_decile</b> when decile is invalid or out of range
	 *
	 * @ApiRoute(
	 *     "/api/advocate-rankings/<decile>[/[<start>-<count>]]",
	 *     parameters={
	 *         "decile"={
	 *             "requirement": "-?\d+",
	 *             "type": "integer",
	 *             "description": "Decile",
	 *         },
	 *         "start"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Specifies where to start.",
	 *             "default": 0
	 *         },
	 *         "count"={
	 *             "requirement": "-?\d+",
	 *             "type": "integer",
	 *             "description": "Specifies how many results to return. Maximum is 100.",
	 *         },
	 *     },
	 *     section="Advocates",
	 *     presenter="API:AdvocateRankings",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws BadRequestException when advocate was not found
	 * @throws AbortException when redirecting/forwarding
	 */
	public function actionRead(int $decile, int $start = 0, int $count = 20) : void
	{
		if ($decile < 1 || $decile > 10) {
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'invalid_decile', 'message' => "Invalid decile [{$decile}]."]);
			return;
		}
		$reverse = (bool) $this->getRequest()->getParameter('reverse');
		$count = max(min($count, 100), 1); // enforce maximum
		$start = max ($start, 0); // enforce minimal start

		$advocates = $this->advocateService->findFromDecile($decile, $start, $count, $reverse);
		$output = [];
		foreach ($advocates as $advocate) {
			$output[] = $this->mapAdvocate($advocate);
		}
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate)
	{
		/** @var AdvocateInfo $currentInfo */
		$currentInfo = $advocate->advocateInfo->get()->fetch();
		return [
			'id_advocate' => $advocate->id,
			'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter),
			'sorting_name' => TemplateFilters::formatSortingName($currentInfo->name, $currentInfo->surname),
		];
	}
}
