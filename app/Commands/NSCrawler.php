<?php
namespace App\Commands;

use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Crawler of Supreme Court.
 */
class NSCrawler extends CauseCrawler
{
	protected function configure()
	{
		$this->setName('app:ns-crawler')
			->setDescription('Crawls data from Supreme Court.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where crawler data are ready for import.'
			);
	}

	public function getCommand($directory)
	{
		return sprintf(
			'java -jar %s --publication-date --directory %s --fetch-attempts %s --from %s --to %s --registry-marks %s 2>&1',
			__DIR__ . '/../../externals/cz-supreme-court-crawler-all-1.0.jar',
			escapeshellarg($directory . '/working'),
			3,
			(new DateTime('Monday previous week'))->format('Y-m-d'),
			(new DateTime("Sunday previous week"))->format('Y-m-d'),
			'CDO,NSČR,ICDO'
		);
	}

}