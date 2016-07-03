<?php
namespace App\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command
{
	protected function configure()
	{
		$this->setName('app:import')
			->setDescription('Imports data from one crawler at the time.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// TBD
	}
}