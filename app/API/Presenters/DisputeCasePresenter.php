<?php
declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Model\Cause\Cause;
use App\Model\Services\CauseService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\CaptchaVerificator;
use App\Utils\MailService;
use DateTimeImmutable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Mail\SendException;
use Nette\Utils\Validators;
use Throwable;
use Tracy\Debugger;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for disputing cases
 *
 * @ApiRoute(
 *     "/api/dispute-case/",
 *     section="Cases",
 * )
 */
class DisputeCasePresenter extends Presenter
{

	/** @var CauseService @inject */
	public $causeService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var MailService @inject */
	public $mailService;

	/** @var CaptchaVerificator @inject */
	public $captchaVerificator;

	/**
	 * Dispute given case (case result, advocate or both).
	 *
	 * Apart from case ID following parameters are expected (and mandatory) in POST params:
	 *  - full_name - an non-empty string with sender full name
	 *  - from - non-empty valid e-mail address of sender
	 *  - content - non-empty text explaining why the tagging(s) should be re-considered
	 *  - disputed_tagging - what is disputed
	 *  - captcha_token - an non-empty captcha token which will be used to validate it
	 *  - datetime - datetime of moment when the disputation happens, see warning below. In DATE_ATOM format
	 *
	 * Field disputed_tagging can contain:
	 *  - <b>case_result</b>
	 *  - <b>advocate</b>
	 *  - <b>both</b>
	 *
	 * Warning: datetime should be the moment of loading taggins (or slightly in the past) as there are no IDs what is disputed.
	 * This time is used to detect when tagging changed while user was filling up the form. In such case fail is inevitable.
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
	 *  - <b>invalid_captcha</b> when captcha is invalid
	 *  - <b>no_advocate_tagging</b> when disputed advocate but there is no such for given case
	 *  - <b>no_case_result_tagging</b> when disputed case_result but there is no such for given case
	 *  - <b>inconsistent</b> when there are taggings newer than given datetime
	 *  - <b>fail</b> when storing fails
	 *  - <b>success</b> when succeeded
	 *
	 * @ApiRoute(
	 *     "/api/dispute-case/<id>",
	 *     parameters={
	 *         "id"={
	 *             "requirement": "\d+",
	 *             "type": "integer",
	 *             "description": "Case ID.",
	 *         },
	 *     },
	 *     section="Cases",
	 *     presenter="API:DisputeCase",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @param int $id Case ID
	 * @throws AbortException when redirection happens
	 * @throws BadRequestException when case not found
	 */
	public function actionCreate(int $id) : void
	{
		// Load data from GET and POST request
		$case = $this->causeService->get($id);
		if (!$case) {
			throw new BadRequestException("No such case [{$id}]", 404);
		}

		$fullname = $this->request->getPost('full_name');
		$from = $this->request->getPost('from');
		$content = $this->request->getPost('content');
		$disputedTagging = $this->request->getPost('disputed_tagging');
		$captchaToken = $this->request->getPost('captcha_token');
		$datetime = $this->parseDatetime($this->request->getPost('datetime'));
		// Validate data
		if (!$fullname || !$from || !Validators::isEmail($from) || !$content || !$disputedTagging || !in_array($disputedTagging, ['case_result', 'advocate', 'both'], true) || !$datetime || !$captchaToken) {
			$this->sendJson(['result' => 'invalid_input']);
		}
		if (!$this->validCaptcha($captchaToken)) {
			$this->sendJson(['result' => 'invalid_captcha']);
		}

		// find what is disputed
		$advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);

		$caseResultTagging = $this->getLatestCaseResultTagging($case);
		// check if disputed thing really exists
		if (($disputedTagging === 'both' || $disputedTagging === 'advocate') && !$advocateTagging) {
			$this->sendJson(['result' => 'no_advocate_tagging']);
		}
		if (($disputedTagging === 'both' || $disputedTagging === 'case_result') && !$caseResultTagging) {
			$this->sendJson(['result' => 'no_case_result_tagging']);
		}

		// check consistency
		if (($disputedTagging === 'both' || $disputedTagging === 'advocate') && $advocateTagging->inserted > $datetime) {
			$this->sendJson(['result' => 'inconsistent']);
		}
		if (($disputedTagging === 'both' || $disputedTagging === 'case_result') && $caseResultTagging->inserted > $datetime) {
			$this->sendJson(['result' => 'inconsistent']);
		}

		// store disputation in invalid status
		try {
			$entity = $this->taggingService->dispute($case, $from, $content, $advocateTagging, $caseResultTagging);
		} catch (Throwable $ex) {
			Debugger::log($ex);
			$this->sendJson(['result' => 'fail']);
		}

		// send email.
		$link = $this->getConfirmLink($entity->email, $entity->code);
		$message = $this->mailService->createMessage('disputation-verification', [
			'case' => $case,
			'link' => $link,
			'deadline' => $entity->validUntil
		]);
		$message->setSubject('[Čeští advokáti]: potvrzení rozporování');
		$message->addTo($from, $fullname);
		$message->setFrom($this->mailService->getNoReplyAddress());
		try {
			$this->mailService->send($message);
		} catch (SendException $exception) {
			$this->sendJson(['result' => 'fail']);
		}
		$this->sendJson(['result' => 'success']);
	}

	private function parseDatetime(?string $value) : ?DateTimeImmutable
	{
		$output = DateTimeImmutable::createFromFormat(DATE_ATOM, $value);
		if ($output instanceof DateTimeImmutable) {
			return $output;
		}
		return null;
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

	private function validCaptcha($token)
	{
		return $this->captchaVerificator->verify($token);
	}

	private function getConfirmLink($email, $code)
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'].'/';

		// TODO
		$link = $protocol . $domainName . '?email=__EMAIL__&code=__CODE__';
		$link = str_replace('__EMAIL__', $email, $link);
		$link = str_replace('__CODE__', $code, $link);
		return $link;
	}
}
