<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Year: 2016
 * Time: 20:33
 * License: GNU GPL
 */

namespace App\Commands;

use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Crawler of Supreme Administrative Court.
 */
class NSSCrawler extends CauseCrawler
{
	protected function configure()
	{
		$this->setName('app:nss-crawler')
			->setDescription('Crawls data from Supreme Administrative Court.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where data crawler will be available for import.'
			);
	}

	public function getCommand($directory)
	{
		return sprintf(
			'%s %s --output-directory %s -l %s 2>&1',
            'python3',
			__DIR__ . '/../../externals/nss_crawler.py',
			escapeshellarg($directory),
			7
		);
	}

}
