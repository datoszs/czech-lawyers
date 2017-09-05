<?php
namespace App\Commands;


use App\Enums\Court;
use App\Utils\JobCommand;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagAdvocates extends Command
{
	const ARGUMENT_COURT = 'court';
	use JobCommand;

	protected function configure()
	{
		$this->setName('app:tag-advocates')
			->setDescription('Tagging advocates for NSS and US')
			->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				'Identificator of court'
			);
	}

	public function getCommand($jobId, $court_id) {
		return sprintf(
			'%s %s %s %s %s 2>&1',
			'python3',
			__DIR__ . '/../../externals/tagger.py',
			$jobId,
			$court_id,
			escapeshellarg(__DIR__)
		);
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput) {
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$this->prepare();
		$output = null;
		$code = 0;
		$command = $this->getCommand($this->jobRun->id, Court::$types[$court]);
		$consoleOutput->writeln($command, __DIR__);
		exec($command, $outputArray, $code);

		$output = implode("\n", $outputArray);
		$consoleOutput->writeln($output);
		if ($code == 0) {
			$this->finalize($code, $output, "Tagged successfully");
		} else {
			$this->finalize($code, $output, "Finished with error!");
		}
		return $code;
	}
}
