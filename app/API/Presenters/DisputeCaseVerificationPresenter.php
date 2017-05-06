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
	 * Successes & errors:
	 *  - Returns HTTP 200 with result <b>success</b> when everything was OK and dispustation was created.
	 *  - Returns HTTP 400 with error <b>invalid_input</b> when input is invalid
	 *  - Returns HTTP 400 with error <b>expired</b> when validation request is expired
	 *  - Returns HTTP 404 with error <b>no_request</b> when no such request found
	 *  - Returns HTTP 400 with error <b>already_validated</b> when the request was already validates
	 *  - Returns HTTP 409 with error <b>inconsistent_already_final</b> when at least of one taggings has final flag (was added meanwhile)
	 *  - Returns HTTP 409 with error <b>inconsistent_changed_meanwhile</b> when at least of of the taggings is differing from disputed state
	 *  - Returns HTTP 500 with error <b>fail</b> when other error state happens
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
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'invalid_input']);
			return;
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
			$this->getHttpResponse()->setCode(404);
			$this->sendJson(['error' => 'no_request']);
			return;
		} catch (AlreadyValidatedDisputeRequestException $exception) {
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'already_validated']);
			return;
		} catch (ExpiredDisputeRequestException $exception) {
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'expired']);
			return;
		} catch (InconsistentStateAlreadyFinalTagging $exception) {
			$this->getHttpResponse()->setCode(409);
			$this->sendJson(['error' => 'inconsistent_already_final']);
			return;
		} catch (InconsistentStateChangedMeanwhile $exception) {
			$this->getHttpResponse()->setCode(409);
			$this->sendJson(['error' => 'inconsistent_changed_meanwhile']);
			return;
		} catch (Throwable $exception) {
			Debugger::log($exception);
			$this->getHttpResponse()->setCode(500);
			$this->sendJson(['error' => 'fail']);
			return;
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
