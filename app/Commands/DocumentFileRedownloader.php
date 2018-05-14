<?php
/**
 * Created by IntelliJ IDEA.
 * User: Xorel
 * Date: 11.04.2018
 * Time: 23:04
 */

namespace App\Commands;

use App\Model\Services\DocumentService;
use App\Utils\JobCommand;
use App\Utils\Validators;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentFileRedownloader extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_DIRECTORY = 'directory';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_DIR = 1;
	const INVALID_CONTENT = 2;
	const NO_DATA = 3;


	/** @var DocumentService @inject */
	public $documentService;

	protected function configure()
	{
		$this->setName('app:redownload-document-file')
			->setDescription('Redownloads document files for NSS court.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where data crawler will be available for import.'
			);
	}

	protected function getCommand($directory, $inputFile)
	{
		return sprintf(
			'%s %s --output-directory %s -s "%s" 2>&1',
			'python3',
			__DIR__ . '/../../externals/nss_crawler.py',
			escapeshellarg($directory),
			$inputFile
		);
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$directory = $input->getArgument(static::ARGUMENT_DIRECTORY);
		$code = 0;
		$output = null;
		$message = null;
		$filePath = $this->prepareDataForDownload($directory);
		if (!FileSystem::isAbsolute($directory)) {
			$message = 'Error: The given path has to be absolute.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!is_dir($directory) || !is_readable($directory)) {
			$message = 'Error: The given path is not directory or not readable.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!$this->validateCrawlerDirectory($directory)) {
			$message = 'Error: The given path is not a crawler directory (with working and result directories) or these directories are not empty.';
			$code = static::INVALID_CONTENT;
		} elseif ($filePath == null) {
			$message = 'Info: Any records for re-download.';
			$code = static::NO_DATA;
		} else {
			// run crawler
			$consoleOutput->writeln("Input file was generated - '" . $filePath . "'.");
			$command = $this->getCommand($directory, $filePath);
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
			// remove source file
			unlink($filePath);
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
		return $code;
	}

	protected function prepareDataForDownload($directory)
	{
		$documents = $this->documentService->findDocumentsWithoutFile();
		if (count($documents) == 0)
			return null;
		$records = implode(";\n", array_column($documents, "record_id"));
		$content = "registry_mark;\n" . $records . ';';
		$filePath = $directory . '/list_for_download.csv';
		$file = fopen($filePath, "w");
		fwrite($file, $content);
		return $filePath;
	}
}
