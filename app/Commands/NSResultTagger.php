<?php
namespace App\Commands;

use App\Enums\CaseResult;
use App\Enums\TaggingStatus;
use App\Model\Cause\Cause;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingCaseResult;
use Nette\Utils\Strings;

class NSResultTagger extends ResultTagger
{

	/** @var TaggingService @inject */
	public $taggingService;

	protected function configure()
	{
		$this->setName('app:ns-result-tagger')
			->setDescription('Tag results for cases from Supreme Court (only non-final and differing).');
	}

	protected function beforeExecute()
	{
		$this->court = $this->courtService->getNS();
	}

	private function prepareTagging(Cause $cause)
	{
		$tagging = new TaggingCaseResult();
		$tagging->isFinal = false;
		$tagging->insertedBy = $this->user;
		$tagging->case = $cause;
		return $tagging;
	}

	private function parseResult($result)
	{
		if ($result == "ZASTAVENO") {
			return CaseResult::RESULT_NEUTRAL;
		} elseif (Strings::contains($result, 'ZR') || Strings::contains($result, 'ZAM') || Strings::contains($result, 'ZM')) {
			return CaseResult::RESULT_POSITIVE;
		} elseif ($result == 'ODMÍTNUTO' || Strings::contains($result, 'OD') /* This also expects not containing ZR|ZAM|ZM */ ) {
			return CaseResult::RESULT_NEGATIVE;
		} else {
			return CaseResult::RESULT_UNKNOWN;
		}
	}

	protected function processCase(Cause $cause)
	{
		if ($cause->officialData) { // First process official data.
			$result = array_unique(array_column($cause->officialData, 'result'));
			if (count($result) != 1) {
				$tagging = $this->prepareTagging($cause);
				$tagging->caseResult = CaseResult::RESULT_UNKNOWN;
				$tagging->debug = sprintf('Unexpected results [%s]', implode(', ', $result));
				$tagging->status = TaggingStatus::STATUS_FAILED;
			} else {
				$tagging = $this->prepareTagging($cause);
				$tagging->caseResult = $this->parseResult($result);
				$tagging->debug = sprintf('Original value: %s', $result[0]);
				$tagging->status = TaggingStatus::STATUS_PROCESSED;
			}
			return $this->taggingService->persistCaseResultIfDiffers($tagging);
		} else {
			// TODO: proceed (fallback) to document parsing
		}
	}
}