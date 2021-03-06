<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use App\Enums\AdvocateStatus;
use App\Enums\CaseResult;
use App\Enums\CaseSuccess;
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

	/** @var ILogger @inject */
	public $auditing;

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
	 *         "location: {
	 *             "lat": 49.1234,
	 *             "long": 16.1234
	 *         },
	 *         "remote_page": "http://vyhledavac.cak.cz/Contact/Details/77b3dbfb-f855-4170-9d5b-dc30757a0204",
	 *         "statistics": {
	 *             "negative": 12,
	 *             "neutral": 2,
	 *             "positive": 59
	 *         },
	 *         "success_statistics": {
	 *             "negative": 64,
	 *             "neutral": 2,
	 *             "positive": 5,
	 *             "unknown": 2
	 *         },
	 *         "court_statistics": {
	 *             "1": {
	 *                 "negative": 11,
	 *                 "neutral": 1,
	 *                 "positive": 57
	 *             },
	 *             "2": {
	 *                 "negative": 1,
	 *                 "neutral": 1,
	 *                 "positive": 2
	 *             }
	 *         }
	 *         "court_success_statistics": {
	 *             "1": {
	 *                 "negative": 63,
	 *                 "neutral": 1,
	 *                 "positive": 3
	 *             },
	 *             "2": {
	 *                 "negative": 1,
	 *                 "neutral": 1,
	 *                 "positive": 2,
	 *                 "unknown": 2
	 *             }
	 *         }
	 *         "advocates_with_same_name": [
	 *             {
	 *                 "id_advocate": 125,
	 *                 "fullname": "Mgr. Petr Omáčka",
	 *                 "residence": {
	 *                     "street": "Františkánská 65",
	 *                     "city": "Brno",
	 *                     "postal_area": "602 00"
	 *                 },
	 *             },
	 *             {
	 *                 "id_advocate": 125,
	 *                 "fullname": "JUDr. Petr Ostrý",
	 *                 "residence": {
	 *                     "street": "Opálkova 2a",
	 *                     "city": "Brno",
	 *                     "postal_area": "635 00"
	 *                 },
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
	 * Available statistics success results (@see CaseSuccess):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *  - <b>unknown</b>
	 *
	 * Note: when advocate is in state <b>removed</b>, then emails field is null.
	 *
	 * Note: when advocate (queried or with same name) is in state <b>removed<b>, then fields street and postal_area as well as location are null.
	 *
	 * Note: both statistics take into account only cases which are relevant for advocates portal.
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
		$successStatistics = $this->taggingService->computeAdvocatesSuccessStatisticsPerCourt([$id]);
		$decile = $this->advocateService->getAdvocateDecile($advocate);
		// Transform to output
		$output = $this->mapAdvocate(
			$advocate,
			$statistics[$advocate->id][TaggingService::ALL] ?? [],
			$successStatistics[$advocate->id][TaggingService::ALL] ?? [],
			$statistics[$advocate->id] ?? [],
			$successStatistics[$advocate->id] ?? [],
			$advocatesOfSameName,
			$decile
		);
		// Auditing
		$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocate->getCurrentName()}] with ID [{$advocate->id}].", AuditedReason::REQUESTED_INDIVIDUAL);
		/** @var Advocate $advocateOfSameName */
		foreach ($advocatesOfSameName as $advocateOfSameName) {
			$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocateOfSameName->getCurrentName()}] with ID [{$advocateOfSameName->id}].", AuditedReason::REQUESTED_BATCH);
		}
		// Send output
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate, array $statistics, array $successStatistics, array $courtStatistics, array $courtSuccessStatistics, array $advocatesOfSameName, ?int $decile)
	{
		unset($courtStatistics[TaggingService::ALL]);
		unset($courtSuccessStatistics[TaggingService::ALL]);
		/** @var AdvocateInfo $currentInfo */
		$currentInfo = $advocate->advocateInfo->get()->fetch();
		return [
			'id_advocate' => $advocate->id,
			'remote_identificator' => $advocate->remoteIdentificator,
			'identification_number' => $advocate->registrationNumber, // intentionally swapped
			'registration_number' => $advocate->identificationNumber,
			'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter),
			'residence' => [
				'street' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->street,
				'city' => $currentInfo->city,
				'postal_area' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->postalArea,
			],
			'emails' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->email,
			'state' => $currentInfo->status,
			'location' => $currentInfo->status !== AdvocateStatus::STATUS_REMOVED && $currentInfo->location ? ['lat' => $currentInfo->location->getLatitude(), 'long' => $currentInfo->location->getLongitude()] : null,
			'remote_page' => sprintf('http://vyhledavac.cak.cz/Contact/Details/%s', $advocate->remoteIdentificator),
			'statistics' => [
				CaseResult::RESULT_NEGATIVE => $statistics[CaseResult::RESULT_NEGATIVE] ?? 0,
				CaseResult::RESULT_NEUTRAL => $statistics[CaseResult::RESULT_NEUTRAL] ?? 0,
				CaseResult::RESULT_POSITIVE => $statistics[CaseResult::RESULT_POSITIVE] ?? 0,
			],
			'success_statistics' => [
				CaseSuccess::RESULT_NEGATIVE => $successStatistics[CaseSuccess::RESULT_NEGATIVE] ?? 0,
				CaseSuccess::RESULT_NEUTRAL => $successStatistics[CaseSuccess::RESULT_NEUTRAL] ?? 0,
				CaseSuccess::RESULT_POSITIVE => $successStatistics[CaseSuccess::RESULT_POSITIVE] ?? 0,
				CaseSuccess::RESULT_UNKNOWN => $successStatistics[CaseSuccess::RESULT_UNKNOWN] ?? 0,
			],
			'court_statistics' => $courtStatistics,
			'court_success_statistics' => $courtSuccessStatistics,
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
				'residence' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : [
					'street' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->street,
					'city' => $currentInfo->city,
					'postal_area' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->postalArea,
				],
			];
		}
		return $output;
	}
}
