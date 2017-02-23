<?php
namespace App\Commands;

use App\Model\Cause\Cause;
use App\Model\Court\Court;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use app\Utils\JobCommand;
use Nette\NotImplementedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract case tagger for advocate tagging (obtains all cases/documents relevant for tagging).
 */
abstract class AdvocateTagger extends Command
{
	use JobCommand;

	/** @var CauseService @inject */
	public $causeService;

	/** @var CourtService @inject */
	public $courtService;

	const RETURN_CODE_SUCCESS = 0;

	/** @var Court */
	protected $court;

	protected function configure()
	{
		throw new NotImplementedException();
	}

	protected function beforeExecute()
	{
	}

	protected function processCase(Cause $cause, string &$output, OutputInterface $consoleOutput)
	{
		throw new NotImplementedException();
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$this->beforeExecute();

		$code = 0;
		$message = '';
		$output = '';

		$persisted = 0;

		$data = $this->causeService->findForAdvocateTagging($this->court);
		foreach ($data as $cause) {
			if ($this->processCase($cause, $output, $consoleOutput)) {
				$output .= sprintf("Tagging advocate to case [%s] of [%s]\n", $cause->registrySign, $cause->court->name);
				$persisted++;
			}
		}

		if ($persisted > 0) {
			$this->causeService->flush();
			$message = sprintf("%s case advocates from [%s] were tagged (or its tagging updated).\n", $persisted, $this->court->name);
			$consoleOutput->write($message);
		} else {
			$message = sprintf("Nothing from [%s] was tagged.\n", $this->court->name);
			$consoleOutput->write($message);
		}

		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}
}
