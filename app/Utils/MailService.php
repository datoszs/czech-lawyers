<?php
namespace App\Utils;


use Nette\Application\Application;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\Strings;

class MailService
{
	const TEMPLATES = 'templates';
	const MAILS = 'mails';

	const NOREPLY = 'noreply';
	const SUPPORT = 'support';

	/** @var string */
	private $templatesDirectory;

	/** @var mixed */
	private $mails = [];

	/** @var IMailer */
	private $mailer;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var Application */
	private $application;

	public function __construct($config, IMailer $mailer, ITemplateFactory $templateFactory, Application $application)
	{
		if (!isset($config[static::TEMPLATES])) {
			throw new InvalidStateException('Mailing: no templates dir specified.');
		}
		if (!isset($config[static::MAILS]) || !is_array($config[static::MAILS])) {
			throw new InvalidStateException('Mailing: no mails specified.');
		}
		if (!isset($config[static::MAILS][static::NOREPLY]) || !isset($config[static::MAILS][static::SUPPORT])) {
			throw new InvalidStateException('Mailing: no mails for noreply and support specified.');
		}
		$this->templatesDirectory = $config['templates'];
		$this->mails = $config['mails'];

		$this->mailer = $mailer;
		$this->templateFactory = $templateFactory;
		$this->application = $application;
	}

	public function getAddress($key)
	{
		return (isset($this->mails[$key])) ? $this->mails[$key] : null;
	}

	public function getNoReplyAddress()
	{
		return $this->getAddress(static::NOREPLY);
	}

	public function getSupportAddress()
	{
		return $this->getAddress(static::SUPPORT);
	}

	public function createMessage($template = null, array $params = array())
	{
		$message = new Message();
		if ($template) {
			$templatePath = realpath($this->templatesDirectory . '/' . $template . '.latte');
			if (!Strings::startsWith($templatePath, $this->templatesDirectory)) {
				throw new InvalidStateException("Mailing: mail template not in templates directory [{$this->templatesDirectory}]!");
			}
			if (!file_exists(realpath($templatePath)) || !is_readable($templatePath)) {
				throw new InvalidStateException("Mailing: no such mail template [{$templatePath}]!");
			}
			/** @var Template $template */
			$template = $this->templateFactory->createTemplate();
			UIMacros::install($template->getLatte()->getCompiler());
			$template->getLatte()->addProvider('uiControl', $this->application->getPresenter());
			$template->setFile($templatePath);
			$template->setParameters($params);
			$message->setHtmlBody($template);
		}

		return $message;
	}


	/**
	 * Sends email.
	 * @param Message $mail
	 * @return void
	 * @throws \Nette\Mail\SendException
	 */
	public function send(Message $mail)
	{
		$this->mailer->send($mail);
	}

}