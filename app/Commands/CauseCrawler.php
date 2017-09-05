<?php
namespace App\Commands;

use app\Utils\Validators;
use App\Utils\JobCommand;
use Nette\NotImplementedException;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract crawler base for all document (cause) crawlers.
 * In descendants implement getCommand and configure methods.
 */
abstract class CauseCrawler extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_DIRECTORY = 'directory';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_DIR = 1;
	const INVALID_CONTENT = 2;

	protected function configure()
	{
		throw new NotImplementedException();
	}

	public function getCommand($directory)
	{
		throw new NotImplementedException();
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$directory = $input->getArgument(static::ARGUMENT_DIRECTORY);
		$code = 0;
		$output = null;
		$message = null;
		if (!FileSystem::isAbsolute($directory)) {
			$message = 'Error: The given path has to be absolute.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!is_dir($directory) || !is_readable($directory)) {
			$message = 'Error: The given path is not directory or not readable.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!$this->validateCrawlerDirectory($directory)) {
			$message = 'Error: The given path is not a crawler directory (with working and result directories) or these directories are not empty.';
			$code = static::INVALID_CONTENT;
		} else {
			// run crawler
			$command = $this->getCommand($directory);
			exec($command, $outputArray, $code);
			$output = implode("\n", $outputArray);
			if ($code !== self::RETURN_CODE_SUCCESS) {
				$message = 'Error: ' . $output;
			} else {
				// move it and empty directory
				rename($directory . '/working', $directory . '/result');
				mkdir($directory . '/working');
				$message = 'Crawled successfully.';
			}
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}
}
