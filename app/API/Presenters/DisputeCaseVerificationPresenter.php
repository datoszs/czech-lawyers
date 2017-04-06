<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Exceptions\ExpiredDisputeRequestException;
use App\Exceptions\NoSuchDisputeRequestException;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
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
			$this->taggingService->confirmDispute($email, $code);
		} catch (NoSuchDisputeRequestException $exception) {
			$this->sendJson(['result' => 'no_request']);
		} catch (ExpiredDisputeRequestException $exception) {
			$this->sendJson(['result' => 'expired']);
		} catch (Throwable $exception) {
			Debugger::log($exception);
			$this->sendJson(['result' => 'fail']);
		}

		$this->sendJson(['result' => 'success']);
	}
}
