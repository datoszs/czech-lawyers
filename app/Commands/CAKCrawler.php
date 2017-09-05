<?php
namespace App\Commands;

use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Crawler of the Czech Bar Association
 */
class CAKCrawler extends CauseCrawler
{
	protected function configure()
	{
		$this->setName('app:cak-crawler')
			->setDescription('Crawls data from Czech bar Association.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where data crawler will be available for import.'
			);
	}

	public function getCommand($directory)
	{
		return sprintf(
			'%s %s --output-directory %s 2>&1',
            'python3',
			__DIR__ . '/../../externals/cak_crawler.py',
			escapeshellarg($directory)
		);
	}

}
