<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Services\AdvocateService;
use App\Utils\TemplateFilters;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for retrieving advocates suggestions (for autocomplete).
 * @ApiRoute(
 *     "/api/advocate/autocomplete",
 *     section="Advocates",
 * )
 */
class AdvocateAutocompletePresenter extends Presenter
{

	/** @var AdvocateService @inject */
	public $advocateService;

	/**
	 * Get up to 30 advocates suggestions according to given search query (search is performed in full name or identification number).
	 * Returns list of matched advocates, matched determines whether the given string was matched in identification number (<b>ic</b>), or name (<b>fullname</b>).
	 *
	 * <json>
	 *     [
	 *         {
	 *             "id_advocate": 123,
	 *             "fullname": "JUDr. Ing. Petr Omáčka, PhD.",
	 *             "matched": {
	 *                 "type": "ic",
	 *                 "value": "11223344"
	 *             }
	 *         }
	 *     ]
	 * </json>
	 *
	 * @ApiRoute(
	 *     "/api/advocate/autocomplete[/<query>]",
	 *     parameters={
	 *         "query"={
	 *             "requirement": ".+",
	 *             "type": "string",
	 *             "description": "Non empty string query to be matched anywhere in advocate name or identification number.",
	 *         }
	 *     },
	 *     section="Advocates",
	 *     presenter="API:AdvocateAutocomplete",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param null|string $query Non empty string query to be matched anywhere in the advocate name or identification number.
	 * @throws AbortException when redirection happens
	 */
	public function actionRead(?string $query) : void
	{
		$output = [];
		if (!$query || Strings::length($query) === 0) {
			$this->sendJson($output);
			return;
		}
		$advocates = $this->advocateService->search($query, 0, 30);
		$output = array_map(function (Advocate $advocate) use ($query) {
			return $this->mapAdvocate($advocate, $query);
		}, $advocates);
		$this->sendJson($output);
	}

	private function mapAdvocate(Advocate $advocate, string $query)
	{
		$currentFullname = null;
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
			$fullname = TemplateFilters::formatName($advocateInfo->name, $advocateInfo->surname, $advocateInfo->degreeBefore, $advocateInfo->degreeAfter, $advocateInfo->city);
			if (!$currentFullname) {
				$currentFullname = $fullname;
			}
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
			'fullname' => $currentFullname,
			'matched' => [
				'type' => $matchedType,
				'value' => $matchedValue
			]
		];
	}
}
