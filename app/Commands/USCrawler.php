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
			'%s %s --output-directory %s %s --date-from="%s" --date-to="%s" 2>&1 && deactivate',
            '/usr/local/share/.virtualenvs/staging-crawler-us/bin/python',
			__DIR__ . '/../../externals/us-crawler.py',
			escapeshellarg($directory),
			'-n',
			(new DateTime('Monday previous week'))->format('d. m. Y'),
			(new DateTime("Sunday previous week"))->format('d. m. Y')
		);
	}

}
