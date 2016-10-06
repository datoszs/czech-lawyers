<?php
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
			'workon staging-crawler-nss && python3 %s --output-directory %s %s --date-from "%s" --date-to "%s" 2>&1 && deactivate',
			__DIR__ . '/../../externals/nss-crawler.py',
			escapeshellarg($directory),
			'-n',
			(new DateTime('Monday previous week'))->format('d. m. Y'),
			(new DateTime("Sunday previous week"))->format('d. m. Y')
		);
	}

}
