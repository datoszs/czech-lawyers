<?php
namespace App\Commands;


use App\Model\Services\AdvocateService;
use app\Utils\JobCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdvocateScoreUpdate extends Command
{
	use JobCommand;

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_ERROR = 1;

	/** @var AdvocateService @inject */
	public $advocateService;

	protected function configure()
	{
		$this->setName('app:advocate-score-update')
			->setDescription('Updates advocate score');
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$code = 0;
		$message = null;
		if ($this->advocateService->updateScores()) {
			$message = 'Advocate scores were update successfully.';
		} else {
			$message = 'Advocate scores update has failed.';
		}
		$consoleOutput->writeln($message);
		$this->finalize($code, $message, $message);
	}
}
