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
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for retrieving list of advocates for search results (more detailed than autocomplete)
 *
 * @ApiRoute(
 *     "/api/advocate/search",
 *     section="Advocates",
 * )
 */
class AdvocateSearchPresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var ILogger @inject */
	public $auditing;

	/**
	 * Get relevant advocates with extended information (search is performed in full name or identification number)
	 * Returns list of matched advocates, matched determines whether the given string was matched in identification number (<b>ic</b>), or name (<b>fullname</b>).
	 *
	 * <json>
	 *     [
	 *         {
	 *             "id_advocate": 123,
	 *             "identification_number": "11223344",
	 *             "fullname": "JUDr. Ing. Petr Omáčka, PhD.",
	 *             "residence": {
	 *                 "street": "Pod mostem",
	 *                 "city": "Brno",
	 *                 "postal_area": "602 00"
	 *             },
	 *             "state": "active",
	 *             "matched": {
	 *                 "type": "ic",
	 *                 "value": "11223344"
	 *             },
	 *             "statistics": {
	 *                 "negative": 12,
	 *                 "neutral": 2,
	 *                 "positive": 59
	 *             },
	 *             "success_statistics": {
	 *                 "negative": 10,
	 *                 "neutral": 2,
	 *                 "positive": 3,
	 *                 "unknown": 2
	 *             }
	 *         }
	 *     ]
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
	 * Available success statistics results (@see CaseResult):
	 *  - <b>negative</b>
	 *  - <b>neutral</b>
	 *  - <b>positive</b>
	 *  - <b>unknown</b>
	 *
	 * Note: when advocate (queried or with same name) is in state <b>removed<b>, then fields street and postal_area are null.
	 *
	 * Note: statistics take into account only cases which are relevant for advocates portal.
	 *
	 * @ApiRoute(
	 *     "/api/advocate/search[/<query>/[<start>-<count>]]",
	 *     parameters={
	 *         "query"={
	 *             "requirement": ".+",
	 *             "type": "string",
	 *             "description": "Non empty string query to be matched anywhere in advocate name or identification number.",
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
	 *     section="Advocates",
	 *     presenter="API:AdvocateSearch",
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
		$count = min($count, 100); // enforce maximum
		$start = max ($start, 0); // enforce minimal start

		$output = [];
		if (!$query || Strings::length($query) === 0) {
			$this->sendJson($output);
			return;
		}
		// Load data
		$advocates = $this->advocateService->search($query, $start, $count);
		$advocatesIds = array_map(function (Advocate $advocate) { return $advocate->id; }, $advocates);
		$statistics = $this->taggingService->computeAdvocatesStatisticsPerCourt($advocatesIds);
		$successStatistics = $this->taggingService->computeAdvocatesSuccessStatisticsPerCourt($advocatesIds);
		// Transform to output
		$output = array_map(function (Advocate $advocate) use ($query, $statistics, $successStatistics) {
			return $this->mapAdvocate($advocate, $query, $statistics[$advocate->id][TaggingService::ALL] ?? [], $successStatistics[$advocate->id][TaggingService::ALL] ?? []);
		}, $advocates);
		// Auditing
		/** @var Advocate $advocate */
		foreach ($advocates as $advocate) {
			$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocate->getCurrentName()}] with ID [{$advocate->id}].", AuditedReason::REQUESTED_BATCH);
		}
		// Send output
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate, string $query, array $statistics, array $successStatistics)
	{
		$currentInfo = null;
		$matchedType = null;
		$matchedValue = null;

		// Check if not matched in identification number
		if ($advocate->registrationNumber === $query) {
			$matchedType = 'ic';
			$matchedValue = $advocate->registrationNumber;
		}
		// Compose name and test if not matched in
		/** @var AdvocateInfo $advocateInfo */
		foreach ($advocate->advocateInfo as $advocateInfo) {
			if (!$currentInfo) {
				$currentInfo = $advocateInfo;
			}
			$fullname = TemplateFilters::formatName($advocateInfo->name, $advocateInfo->surname, $advocateInfo->degreeBefore, $advocateInfo->degreeAfter);
			if (!$matchedType && stripos($fullname, $query) !== false) {
				$matchedType = 'fullname';
				$matchedValue = $fullname;
			}
			if ($matchedType) {
				break;
			}
		}
		return [
			'id_advocate' => $advocate->id,
			'identification_number' => $advocate->registrationNumber,
			'fullname' => TemplateFilters::formatName($currentInfo->name, $currentInfo->surname, $currentInfo->degreeBefore, $currentInfo->degreeAfter),
			'residence' => [
				'street' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->street,
				'city' => $currentInfo->city,
				'postal_area' => $currentInfo->status === AdvocateStatus::STATUS_REMOVED ? null : $currentInfo->postalArea,
			],
			'state' => $currentInfo->status,
			'matched' => [
				'type' => $matchedType,
				'value' => $matchedValue
			],
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
			]
		];
	}
}
