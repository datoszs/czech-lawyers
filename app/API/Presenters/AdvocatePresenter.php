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
	 * @ApiRoute(
	 *     "/api/advocate/<id>",
	 *     parameters={
	 *         "id"={
	 *             "requirement": "\d+",
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
	 * @throws AbortException when redirection happens
	 */
	public function actionRead(int $id) : void
	{
		// Load data
		$advocate = $this->advocateService->get($id);
		$statistics = $this->taggingService->computeAdvocatesStatistics([$id]);
		// Transform to output
		$output = $this->mapAdvocate($advocate, $statistics[$advocate->id] ?? []);
		// Send output
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate, array $statistics)
	{
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
			]
		];
	}
}
