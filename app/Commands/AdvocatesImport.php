<?php
namespace App\Commands;


use App\Enums\AdvocateStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Services\AdvocateService;
use App\Utils\Helpers;
use App\Utils\Normalize;
use App\Utils\Validators;
use app\Utils\JobCommand;
use League\Csv\Reader;
use Nette\InvalidArgumentException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdvocatesImport extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_DIRECTORY = 'directory';

	const ADVOCATES_DIRECTORY_PROJECT_RELATIVE = 'storage/advocates/';
	const ADVOCATES_DIRECTORY = __DIR__ . '/../../storage/advocates/';

	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_DIR = 1;
	const INVALID_CONTENT = 2;

	/** @var AdvocateService @inject */
	public $advocateService;

	protected function configure()
	{
		$this->setName('app:import-advocates')
			->setDescription('Imports data from advocate crawler.')
			->addArgument(
				static::ARGUMENT_DIRECTORY,
				InputArgument::REQUIRED,
				'Directory where final crawler data are ready for import. (The result directory)'
			);
	}

	/**
	 * Expects valid directory with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param string $output output to be stored
	 * @param string $directory
	 * @return array where first is number of newly created (imported), second number of updated items and third number of duplicate.
	 */
	public function processDirectory(OutputInterface $consoleOutput, &$output, $directory)
	{
		$csv = Reader::createFromPath($directory . '/metadata.csv');
		$csv->setDelimiter(';');
		$destinationDir = static::ADVOCATES_DIRECTORY;
		$destinationDirRelative = static::ADVOCATES_DIRECTORY_PROJECT_RELATIVE;
		$csv->setOffset(1); // skip column names
		$rows = $csv->fetchAssoc($this->getColumnNames());
		$imported = 0;
		$updated = 0;
		$duplicated = 0;
		foreach ($rows as $row) {
			// First prepare data, we will need then in any case
			$advocateInfo = new AdvocateInfo();
			$advocateInfo->status = $this->getState($row['state']);
			$advocateInfo->name = $row['name'];
			$advocateInfo->surname = $row['surname'];
			$advocateInfo->degreeBefore = $row['degree_before'];
			$advocateInfo->degreeAfter = $row['degree_after'];
			$advocateInfo->street = $row['street'];
			$advocateInfo->city = $row['city'];
			$advocateInfo->postalArea = $row['postal_area'];
			$advocateInfo->email = Helpers::safeDeterministicExplode(', ', $row['email']);
			$advocateInfo->specialization = Helpers::safeDeterministicExplode('|', $row['specialization']);
			$advocateInfo->company = $row['company'];
			$advocateInfo->dataBox = $row['data_box'];
			$advocateInfo->exOffo = $row['ex_offo'];
			$advocateInfo->wayOfPracticingAdvocacy = $row['way_of_practicing_advocacy'];
			$advocateInfo->insertedBy = $this->user;

			$advocate = new Advocate();
			$advocate->remoteIdentificator = $row['remote_identificator'];
			$advocate->identificationNumber = $row['identification_number'];
			$advocate->registrationNumber = $row['registration_number'];
			$advocate->localPath = $row['local_path'];
			$advocate->insertedBy = $this->user;
			$advocate->advocateInfo->add($advocateInfo);
			$advocate->jobRun = $this->jobRun;

			// Check if not duplicate
			$identificationNumber = Normalize::identificationNumber($row['identification_number']);
			/** @var Advocate $entity */
			$entity = $this->advocateService->findByIdentificationNumber($identificationNumber);
			if (!$entity) { // When not found, check also remote identificator (EČ - identification number should be stable except for foreign advocates gaining it later. In such case we need to fallback to remote identificator, see #57).
				$entity = $this->advocateService->findbyRemoteIdentificator($row['remote_identificator']);
				if ($entity) {
					$temp = sprintf("Warning: updating record with ID %s to new EČ %s.\n", $row['remote_identificator'], $identificationNumber);
					$output .= $temp;
					$consoleOutput->write($temp);
					$entity->identificationNumber = $identificationNumber;
					$this->advocateService->persist($entity);
				}
			}

			if ($entity) { // already exists, just update
				/** @var AdvocateInfo $advocateInfoStored */
				$advocateInfoStored = $entity->advocateInfo->get()->fetch();
				if ($advocateInfoStored === null || $this->AdvocateInfosDiffers($advocateInfoStored, $advocateInfo)) {
					$updated++;
					$temp = sprintf("Info: updating record with ID %s (inserting new advocate info tuple).\n", $row['remote_identificator']);
					$output .= $temp;
					$consoleOutput->write($temp);
					$advocateInfo->advocate = $entity;
					// Update remote_identificator
					if ($entity->remoteIdentificator != $row['remote_identificator']) {
						$entity->remoteIdentificator = $row['remote_identificator'];
						$this->advocateService->persist($entity);
					}
					// Invalidate old records
					$this->advocateService->invalidateOldInfos($entity, $advocateInfo);
					$this->advocateService->persist($advocateInfo);
					// Copy new advocate info file into destination folder
					$destinationPath = $destinationDir . $row['local_path'];
					copy($directory . '/documents/' . $row['local_path'], $destinationPath); // copy immediately - better to have not referenced files than documents in database without their files.
				} else {
					$duplicated++;
					$temp = sprintf("Info: record with ID %s already found in database.\n", $row['remote_identificator']);
					$output .= $temp;
					$consoleOutput->write($temp);
				}
				continue;
			}

			// Store to database
			$advocateInfo->advocate = ($entity) ? $entity : $advocate;
			$this->advocateService->insert($advocate, $advocateInfo);
			$imported++;
			// Copy advocate info file into destination folder
			$destinationPath = $destinationDir . $row['local_path'];
			copy($directory . '/documents/' . $row['local_path'], $destinationPath); // copy immediately - better to have not referenced files than documents in database without their files.
		}
		return [$imported, $updated, $duplicated];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$directory = $input->getArgument(static::ARGUMENT_DIRECTORY);
		$code = 0;
		$output = null;
		$message = null;
		if (!FileSystem::isAbsolute($directory)) {
			$message = 'Error: The given path has to be absolute.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!is_dir($directory) || !is_readable($directory)) {
			$message = 'Error: The given path is not directory or not readable.';
			$code = static::RETURN_CODE_INVALID_DIR;
		} elseif (!$this->validateInputDirectory($directory)) {
			$message = 'Error: The given path doesn\'t contain metadata.csv or documents... or contains another files/directories.';
			$code = static::INVALID_CONTENT;
		} else {
			// import to db
			list($imported, $updated, $duplicated) = $this->processDirectory($consoleOutput, $output, $directory);
			if ($imported > 0 || $updated > 0) {
				$this->advocateService->flush();
			}
			$temp = $message = "Imported {$imported} new advocates, {$updated} updated, {$duplicated} remained the same.\n";
			$consoleOutput->write($temp);
			$output .= $temp;
			// Empty directory after successful procession
			FileSystem::delete($directory . '/metadata.csv');
			FileSystem::delete($directory . '/documents/');
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
	}

	private function getColumnNames()
	{
		return [
			'remote_identificator',
			'identification_number',
			'registration_number',
			'name',
			'surname',
			'degree_before',
			'degree_after',
			'state',
			'street',
			'city',
			'postal_area',
			'local_path',
			'email',
			'specialization',
			'company',
			'data_box',
			'ex_offo',
			'way_of_practicing_advocacy'
		];
	}

	private function getState($stateName)
	{
		$stateName = Strings::lower($stateName);
		switch ($stateName) {
			case 'aktivní':
				return AdvocateStatus::STATUS_ACTIVE;
			case 'vyškrtnutý':
				return AdvocateStatus::STATUS_REMOVED;
			case 'pozastavený':
				return AdvocateStatus::STATUS_SUSPENDED;
			case 'vytvořený':
				return AdvocateStatus::STATUS_CREATED;
			default:
				throw new InvalidArgumentException("Invalid advocate state [${$stateName}].");
		}
	}

	private function AdvocateInfosDiffers(AdvocateInfo $current, AdvocateInfo $new)
	{
		return
			$current->status != $new->status ||
			$current->name != $new->name ||
			$current->surname != $new->surname ||
			$current->degreeAfter != $new->degreeAfter ||
			$current->degreeBefore != $new->degreeBefore ||
			$current->street != $new->street ||
			$current->city != $new->city ||
			$current->postalArea != $new->postalArea ||
			$current->email != $new->email ||
			$current->specialization != $new->specialization ||
			$current->company != $new->company ||
			$current->dataBox != $new->dataBox ||
			$current->exOffo != $new->exOffo ||
			$current->wayOfPracticingAdvocacy != $new->wayOfPracticingAdvocacy
			;
	}
}
