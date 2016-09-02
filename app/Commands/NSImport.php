<?php
namespace App\Commands;


use App\Model\Documents\Document;
use App\Model\Documents\DocumentSupremeCourt;
use App\Utils\Normalize;
use League\Csv\Reader;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputArgument;

class NSImport extends CausaImport
{

	protected function configure()
	{
		$this->setName('app:import')
			->setDescription('Imports data from one crawler at the time.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where final crawler data are ready for import. (The result directory)'
			);
	}

	protected function processData($directory)
	{
		$court = $this->courtService->getNS();
		$csv = Reader::createFromPath($directory . '/metadata.csv');
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAssoc(['court_name', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'ecli', 'decision_type']);
		$imported = 0;
		$duplicated = 0;
		foreach ($rows as $row) {
			var_dump($row);
			$document = new Document();
			$document->court = $court;
			$document->webPath = (string) $row['web_path'];
			$document->localPath = (string) $row['local_path'];
			$document->decisionDate = new DateTime($row['decision_date']);
			$document->case = $this->causeService->findOrCreate(Normalize::registryMark($row['registry_mark']));
			$documentSupremeCourt = new DocumentSupremeCourt();
			$documentSupremeCourt->ecli = $row['ecli'];
			// TOdo check if document not duplicate
			$documentSupremeCourt->decisionType = $row['decision_type'];
			$documentSupremeCourt->document = $document;
			$this->documentService->insert($document, $documentSupremeCourt);
			$imported++;
		}
		return [$imported, $duplicated];
	}
}