<?php
namespace App\Commands;

use App\Utils\JobCommand;
use App\Utils\MailService;
use Nette\Mail\IMailer;
use Nette\Mail\SendException;
use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendBatchEmail extends Command
{
	use JobCommand;

	/** @var IMailer @inject */
	public $mailer;

	/** @var MailService @inject */
	public $mailService;

	protected function configure()
	{
		$this->setName('app:send-batch-email')
			->setDescription('Send batch email from given file in standard template')
			->addArgument(
				'recipients',
				InputArgument::REQUIRED,
				'File with recipients, one per line.'
			)->addArgument(
				'template',
				InputArgument::REQUIRED,
				'File with content of the e-mail.'
			)->addArgument(
				'subject',
				InputArgument::REQUIRED,
				'Subject of the e-mail'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare(false);

		$recipients = $input->getArgument('recipients');
		$template = $input->getArgument('template');
		$subject = $input->getArgument('subject');

		if (!file_exists($recipients)) {
			$consoleOutput->writeln('File with recipients doesn\'t exists.');
			exit(1);
		}
		$toSend = explode("\n", file_get_contents($recipients));
		if (count($toSend) === 0 || !$toSend[0]) {
			$consoleOutput->writeln('File with recipients doesn\'t contain any e-mail.');
			exit(2);
		}
		if (!file_exists($template)) {
			$consoleOutput->writeln('File with template doesn\'t exists.');
			exit(3);
		}
		$template = file_get_contents($template);
		if (strlen($template) < 50) {
			$consoleOutput->writeln('File with template seems shorter than 50 characters, that is probably a mistake.');
			exit(4);
		}

		if (!$subject) {
			$consoleOutput->writeln('Subject has to be filled.');
			exit(5);
		}

		$sent = 0;
		$failed = 0;
		foreach ($toSend as $email) {
			if (!$email) {
				continue;
			}
			if (!Validators::isEmail($email)) {
				$consoleOutput->write('NOT VALID');
			} else {
				$consoleOutput->write($email . '... ');
				$message = $this->mailService->createMessage('free', ['content' => $template]);
				$message->setSubject('[Čeští advokáti]: ' . $subject);
				$message->addTo($email);
				$message->setFrom($this->mailService->getNoReplyAddress());
				try {
					$this->mailService->send($message);
					$sent++;
					$consoleOutput->write('SEND');
				} catch (SendException $exception) {
					$failed++;
					$consoleOutput->write('FAILED');
				}
			}
			$consoleOutput->writeln('');
		}
		$consoleOutput->writeln('------------------------');
		$consoleOutput->writeln('SENT: ' . $sent);
		$consoleOutput->writeln('FAILED: ' . $failed);
		return 0;
	}
}
