<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Exceptions\AlreadyValidatedDisputeRequestException;
use App\Exceptions\ExpiredDisputeRequestException;
use App\Exceptions\InconsistentStateAlreadyFinalTagging;
use App\Exceptions\InconsistentStateChangedMeanwhile;
use App\Exceptions\NoSuchDisputeRequestException;
use App\Model\Cause\Cause;
use App\Model\Services\CauseService;
use App\Model\Services\DisputationService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\MailService;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Validators;
use Throwable;
use Tracy\Debugger;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * * API for disputing cases
 *
 * @ApiRoute(
 *     "/api/dispute-case/",
 *     section="Cases",
 * )
 */
class DisputeCaseVerificationPresenter extends Presenter
{

	/** @var CauseService @inject */
	public $causeService;

	/** @var DisputationService @inject */
	public $disputationService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var MailService @inject */
	public $mailService;

	/**
	 * Verifies pending case disputation
	 *
	 * Apart from case ID following parameters are expected (and mandatory) in POST params:
	 *  - email - non-empty e-mail
	 *  - code - non-empty validation code
	 *
	 *
	 * Outcome:
	 *
	 * <json>
	 *     {
	 *         "result": "success"
	 *     }
	 * </json>
	 *
	 * Potential results of disputing:
	 *  - <b>invalid_input</b> when input is invalid
	 *  - <b>expired</b> when validation request is expired
	 *  - <b>no_request</b> when no such request found
	 *  - <b>already_validates</b> when the request was already validates
	 *  - <b>inconsistent_already_final</b> when at least of one taggings has final flag (was added meanwhile)
	 *  - <b>inconsistent_changed_meanwhile</b> when at least of of the taggings is differing from disputed state
	 *  - <b>success</b> when succeeded
	 *  - <b>fail</b> when other error state happens
	 *
	 * @ApiRoute(
	 *     "/api/dispute-case-verification/",
	 *     section="Cases",
	 *     presenter="API:DisputeCaseVerification",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws AbortException when redirection happens
	 */
	public function actionCreate() : void
	{
		// Load data from POST
		$email = $this->request->getPost('email');
		$code = $this->request->getPost('code');
		// Validate data
		if (!$email || !Validators::isEmail($email) || !$code) {
			$this->sendJson(['result' => 'invalid_input']);
		}

		// Confirm dispute
		try {
			$dispute = $this->disputationService->getDispute($email, $code);

			$advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($dispute->case);
			$caseResultTagging = $this->getLatestCaseResultTagging($dispute->case);
			if (
				($dispute->taggingAdvocate && $advocateTagging && $advocateTagging->isFinal) ||
				($dispute->taggingCaseResult && $caseResultTagging && $caseResultTagging->isFinal)
			) {
				throw new InconsistentStateAlreadyFinalTagging();
			}
			if (
				($dispute->taggingAdvocate && $dispute->taggingAdvocate->advocate !== $advocateTagging->advocate) ||
				($dispute->taggingCaseResult && $dispute->taggingCaseResult->caseResult !== $caseResultTagging->caseResult)
			) {
				throw new InconsistentStateChangedMeanwhile();
			}
			$this->disputationService->confirmDispute($email, $code);
		} catch (NoSuchDisputeRequestException $exception) {
			$this->sendJson(['result' => 'no_request']);
		} catch (AlreadyValidatedDisputeRequestException $exception) {
			$this->sendJson(['result' => 'already_validated']);
		} catch (ExpiredDisputeRequestException $exception) {
			$this->sendJson(['result' => 'expired']);
		} catch (InconsistentStateAlreadyFinalTagging $exception) {
			$this->sendJson(['result' => 'inconsistent_already_final']);
		} catch (InconsistentStateChangedMeanwhile $exception) {
			$this->sendJson(['result' => 'inconsistent_changed_meanwhile']);
		} catch (Throwable $exception) {
			Debugger::log($exception);
			$this->sendJson(['result' => 'fail']);
		}

		$this->sendJson(['result' => 'success']);
	}

	/**
	 * @param Cause $case
	 * @return TaggingCaseResult|null
	 */
	private function getLatestCaseResultTagging(Cause $case)
	{
		$taggings = $this->taggingService->findCaseResultLatestTaggingByCases([$case]);
		if ($taggings) {
			return reset($taggings);
		}
		return null;
	}
}
