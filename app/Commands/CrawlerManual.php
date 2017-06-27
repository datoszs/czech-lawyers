<?php
/**
 * Created by IntelliJ IDEA.
 * User: Radim Jílek
 * Year: 2017
 * Time: 16:15
 * License: GNU GPL
 */

namespace App\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Crawler of Supreme Administrative Court.
 */
class CrawlerManual extends Command
{

	const ARGUMENT_COURT = 'court';
	const ARGUMENT_FROM = 'from';
	const ARGUMENT_TO = 'to';

	protected function configure()
	{
		$this->setName('app:crawler-manual')
			->setDescription('Crawls data from court with from and to date.')
			->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				'shortcut of court'
			)
			->addArgument(
				static::ARGUMENT_FROM,
				InputArgument::REQUIRED,
				'Date from in "europe" format'
			)
			->addArgument(
				static::ARGUMENT_TO,
				InputArgument::REQUIRED,
				'Date to in "europe" format'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$from = $input->getArgument(static::ARGUMENT_FROM);
		$to = $input->getArgument(static::ARGUMENT_TO);

		$command = sprintf(
			'%s %s_crawler.py --date-from %s --date-to %s --output-directory %s --progress-bar',
			'python3',
			__DIR__ . '/../../externals/' . $court,
			$from,
			$to,
			escapeshellarg('/home/cestiadvokati.cz/crawlers-devel/'.$court)
		);
		exec($command);
	}

}
