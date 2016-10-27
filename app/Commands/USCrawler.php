<?php
namespace App\Commands;

use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Crawler of Supreme Administrative Court.
 */
class USCrawler extends CauseCrawler
{
	protected function configure()
	{
		$this->setName('app:us-crawler')
			->setDescription('Crawls data from Constitutional Court.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where data crawler will be available for import.'
			);
	}

	public function getCommand($directory)
	{
		return sprintf(
			'%s %s --output-directory %s -l %s 2>&1 && deactivate',
            'workon staging-crawler-us && python3',
			__DIR__ . '/../../externals/us-crawler.py',
			escapeshellarg($directory),
			7
		);
	}

}
