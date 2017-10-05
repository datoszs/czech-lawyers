<?php
namespace App\Commands;


use App\Enums\Court;
use App\Utils\JobCommand;
use Exception;
use Nette\Utils\FileSystem;
use League\Csv\Reader;
use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagAdvocates extends Command
{
	const ARGUMENT_COURT = 'court';
	const LOG_PATH = __DIR__ . '/../../externals/log_tagger/';
	const LOG_FILE = 'tagger_log.csv';
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

	protected function auditing() {
		$transactionLogger = $this->auditing->createTransactionLogger();
		$path = realpath(static::LOG_PATH . static::LOG_FILE);
		//echo $path;
		if (file_exists($path)) {
			$csv = Reader::createFromPath($path);
			$csv->setDelimiter('@');

			$csv->setOffset(1); // skip column names
			$rows = $csv->fetchAssoc(['audited_subject', 'description', 'reason']);
			foreach ($rows as $row) {
				$audited_subject = ($row['audited_subject'] == "CASE_TAGGING") ? AuditedSubject::CASE_TAGGING : null;
				$reason = ($row['reason'] == "SCHEDULED") ? AuditedReason::SCHEDULED : null;
				if ($audited_subject && $reason)
					$transactionLogger->logCreate($audited_subject, $row['description'], $reason);

			}
			$transactionLogger->commit();
			$csv = $rows = $audited_subject = $reason = null; // important before delete CSV file

			// Empty directory after successful procession
			if (file_exists($path)) {
				FileSystem::delete($path);
			}
		}
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

		$this->auditing();
		return $code;
	}
}
