<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Enums\AdvocateStatus;
use App\Enums\CaseResult;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Services\AdvocateService;
use App\Model\Services\TaggingService;
use App\Utils\TemplateFilters;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining information about advocate
 *
 * @ApiRoute(
 *     "/api/advocate/",
 *     section="Advocates",
 * )
 */
class AdvocatePresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	/**
	 * Get information about advocate with given ID.
	 *
	 * <json>
	 *     {
	 *         "id_advocate": 123,
	 *         "remote_identificator": "77b3dbfb-f855-4170-9d5b-dc30757a0204",
	 *         "identification_number": "11223344",
	 *         "registration_number": "00001",
	 *         "fullname": "JUDr. Ing. Petr Omáčka, PhD.",
	 *         "residence": {
	 *             "street": "Pod mostem",
	 *             "city": "Brno",
	 *             "postal_area": "602 00"
	 *         },
	 *         "emails": [
	 *             "petr.omacka@example.com"
	 *         ],
	 *         "state": "active",
	 *         "remote_page": "http://vyhledavac.cak.cz/Units/_Search/Details/detailAdvokat.aspx?id=77b3dbfb-f855-4170-9d5b-dc30757a0204",
	 *         "statistics": {
	 *             "negative": 12,
	 *             "neutral": 2,
	 *             "positive": 59,
	 *         },
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
	 *             }
	 *         }
	 *         "advocates_with_same_name": [
	 *             {
	 *                 "id_advocate": 125,
	 *                 "fullname": "Mgr. Petr Omáčka"
	 *             },
	 *             {
	 *                 "id_advocate": 125,
	 *                 "fullname": "JUDr. Petr Ostrý"
	 *             },
	 *         ]
	 *         "rankings": {
	 *             "decile": 2
	 *         }
	 *     }
	 * </json>
	 *
	 * Available advocate states (see @see AdvocateStatus):
	 *  - <b>active</b>
	 *  - <b>suspended</b>
	 *  - <b>removed</b>
	 *
	 * Available statistics results (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *
	 * Note: statistics take into account only cases which are relevant for advocates portal.
	 *
	 * Note: advocates with same name also match historic names on both sides, but shows only up-to-date names.
	 * In example Petr Ostrý was previously named Petr Omáčka or the queried advocate was names Petr Ostrý.
	 * Match is performed on names only (without degrees).
	 *
	 * Errors:
	 *  - Returns HTTP 404 with error <b>no_advocate</b> when such advocate doesn't exist
	 *
	 * @ApiRoute(
	 *     "/api/advocate/<id>",
	 *     parameters={
	 *         "id"={
	 *             "requirement": "-?\d+",
	 *             "type": "integer",
	 *             "description": "Advocate ID.",
	 *         },
	 *     },
	 *     section="Advocates",
	 *     presenter="API:Advocate",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param int $id Advocate ID
	 * @throws AbortException when redirecting/forwarding
	 * @throws BadRequestException when advocate was not found
	 */
	public function actionRead(int $id) : void
	{
		// Load data
		$advocate = $this->advocateService->get($id);
		if (!$advocate) {
			$this->getHttpResponse()->setCode(404);
			$this->sendJson(['error' => 'no_advocate', 'message' => "No such advocate [{$id}]"]);
			return;
		}
		$advocatesOfSameName = $this->advocateService->findOfSameName($advocate);
		$statistics = $this->taggingService->computeAdvocatesStatisticsPerCourt([$id]);
		$decile = $this->advocateService->getAdvocateDecile($advocate);
		// Transform to output
		$output = $this->mapAdvocate($advocate, $statistics[$advocate->id][TaggingService::ALL] ?? [], $statistics[$advocate->id] ?? [], $advocatesOfSameName, $decile);
		// Send output
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate, array $statistics, array $courtStatistics, array $advocatesOfSameName, ?int $decile)
	{
		unset($courtStatistics[TaggingService::ALL]);
		/** @var AdvocateInfo $currentInfo */
		$currentInfo = $advocate->advocateInfo->get()->fetch();
		return [
			'id_advocate' => $advocate->id,
			'remote_identificator' => $advocate->remoteIdentificator,
			'identification_number' => $advocate->registrationNumber, // intentionally swapped
			'registration_number' => $advocate->identificationNumber,
			'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter),
			'residence' => [
				'street' => $currentInfo->street,
				'city' => $currentInfo->city,
				'postal_area' => $currentInfo->postalArea,
			],
			'emails' => $currentInfo->email,
			'state' => $currentInfo->status,
			'remote_page' => sprintf('http://vyhledavac.cak.cz/Units/_Search/Details/detailAdvokat.aspx?id=%s', $advocate->remoteIdentificator),
			'statistics' => [
				CaseResult::RESULT_NEGATIVE => $statistics[CaseResult::RESULT_NEGATIVE] ?? 0,
				CaseResult::RESULT_NEUTRAL => $statistics[CaseResult::RESULT_NEUTRAL] ?? 0,
				CaseResult::RESULT_POSITIVE => $statistics[CaseResult::RESULT_POSITIVE] ?? 0,
			],
			'court_statistics' => $courtStatistics,
			'advocates_with_same_name' => $this->mapAdvocateWithSameName($advocatesOfSameName),
			'rankings' => [
				'decile' => $decile
			]
		];
	}

	private function mapAdvocateWithSameName(array $advocatesOfSameName)
	{
		$output = [];
		/** @var Advocate $advocate */
		foreach ($advocatesOfSameName as $advocate) {
			/** @var AdvocateInfo $currentInfo */
			$currentInfo = $advocate->advocateInfo->get()->fetch();
			$output[] = [
				'id_advocate' => $advocate->id,
				'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter),
			];
		}
		return $output;
	}
}
