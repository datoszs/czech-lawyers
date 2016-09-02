<?php
namespace App\Commands;


use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Model\Services\DocumentService;
use App\Utils\Validators;
use app\Utils\JobCommand;
use Nette\NotImplementedException;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CausaImport extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_DIRECTORY = 'directory';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_DIR = 1;
	const INVALID_CONTENT = 2;

	/** @var CourtService @inject */
	public $courtService;
	/** @var DocumentService @inject */
	public $documentService;
	/** @var CauseService @inject */
	public $causeService;

	protected function configure()
	{
		throw new NotImplementedException();
	}

	/**
	 * Expects validate directory with data to import
	 * @param $directory
	 * @return array where first is number of imported and second number of duplicated items.
	 */
	public function processDirectory($directory)
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
			$consoleOutput->writeln($message);
		} elseif (!is_dir($directory) || !is_readable($directory)) {
			$message = 'Error: The given path is not directory or not readable.';
			$code = static::RETURN_CODE_INVALID_DIR;
			$consoleOutput->writeln($message);
		} elseif (!$this->validateInputDirectory($directory)) {
			$message = 'Error: The given path doesn\'t contain metadata.csv or documents... or contains another files/directories.';
			$code = static::INVALID_CONTENT;
			$consoleOutput->writeln($message);
		} else {
			// import to db
			list($imported, $duplicated) = $this->processDirectory($directory);
			$message = "Imported {$imported} documents ({$duplicated} duplicate).";
			// Empty directory after successful procession
			unlink($directory . '/result');
			mkdir($directory . '/result');
		}
		$this->finalize($code, $output, $message);
	}
}