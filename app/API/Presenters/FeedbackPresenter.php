<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Utils\CaptchaVerificator;
use App\Utils\MailService;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Mail\SendException;
use Nette\Utils\Validators;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for sending feedback (contacting us)
 *
 * @ApiRoute(
 *     "/api/feedback/",
 *     section="Feedback",
 * )
 */
class FeedbackPresenter extends Presenter
{

	/** @var MailService @inject */
	public $mailService;

	/** @var CaptchaVerificator @inject */
	public $captchaVerificator;

	/**
	 * Sends given feedback to responsible people.
	 *
	 * Expects following POST params:
	 *  - full_name - an non-empty string with sender full name
	 *  - from - an valid e-mail address of sender
	 *  - content - non empty text to be sent to us
	 *  - captcha_token - an non-empty captcha token which will be used to validate the request (to prevent spamming)
	 *
	 * Outcome:
	 *
	 * <json>
	 *     {
	 *         "result": "success"
	 *     }
	 * </json>
	 *
	 * Potential results of sending feedback:
	 *  - Returns HTTP 200 with result <b>success</b> when sending succeed
	 *  - Returns HTTP 400 with error <b>invalid_input</b> when input data missing or invalid
	 *  - Returns HTTP 401 with error <b>invalid_captcha</b> when captcha is invalid
	 *  - Returns HTTP 500 with error <b>fail</b> when sending fails
	 *
	 * @ApiRoute(
	 *     "/api/feedback/",
	 *     section="Feedback",
	 *     presenter="API:Feedback",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws AbortException when redirection happens
	 */
	public function actionCreate() : void
	{
		// Load data from POST request
		$fullname = $this->request->getPost('full_name');
		$from = $this->request->getPost('from');
		$content = $this->request->getPost('content');
		$captchaToken = $this->request->getPost('captcha_token');
		// Validate data
		if (!$fullname || !$from || !Validators::isEmail($from) || !$content || !$captchaToken) {
			$this->getHttpResponse()->setCode(400);
			$this->sendJson(['error' => 'invalid_input']);
			return;
		}
		if (!$this->validCaptcha($captchaToken)) {
			$this->getHttpResponse()->setCode(401);
			$this->sendJson(['error' => 'invalid_captcha']);
			return;
		}
		// Send e-mail
		$message = $this->mailService->createMessage('feedback', ['content' => $content]);
		$message->setSubject('[Čeští advokáti]: zpráva z webu');
		$message->addTo($this->mailService->getSupportAddress());
		$message->setFrom($this->mailService->getNoReplyAddress());
		$message->addReplyTo($from, $fullname);
		try {
			$this->mailService->send($message);
		} catch (SendException $exception) {
			$this->getHttpResponse()->setCode(500);
			$this->sendJson(['error' => 'fail']);
			return;
		}
		$this->sendJson(['result' => 'success']);
	}
	private function validCaptcha($token)
	{
		return $this->captchaVerificator->verify($token);
	}
}
