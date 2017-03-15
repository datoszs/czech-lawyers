<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Date: 14.03.2017
 * Time: 22:00
 */

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
			'workon cestiadvokati.cz && python',
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
		$command = $this->getCommand($this->jobId, Court::$types[$court]);
		$consoleOutput->writeln($command, __DIR__);
		exec($command, $outputArray, $code);
		$output = implode("\n", $outputArray);
		$consoleOutput->writeln($output);
		$this->finalize($code, $output, "ok");
		return $code;
	}
}
