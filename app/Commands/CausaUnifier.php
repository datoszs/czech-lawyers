<?php
namespace App\Commands;


use App\Enums\Court;
use App\Model\Cause\Cause;
use App\Model\Court\Court as CourtEntity;
use App\Model\Documents\Document;
use App\Model\Services\CauseService;
use App\Model\Services\CourtService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\Normalize;
use App\Utils\Validators;
use app\Utils\JobCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CausaUnifier extends Command
{
	use JobCommand;
	use Validators;

	const ARGUMENT_COURT = 'court';
	const DRY_RUN = 'dry-run';
	const DRY_RUN_SHORTCUT = 'd';


	const RETURN_CODE_SUCCESS = 0;
	const RETURN_CODE_INVALID_COURT = 1;

	/** @var CourtService @inject */
	public $courtService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var CauseService @inject */
	public $causeService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var CourtEntity */
	private $court;

	protected function configure()
	{
		$this->setName('app:unify-cases')
			->setDescription('Walks through all cases of given court and unify their registry marks (and fix relating stuff).')
			->addOption(
				static::DRY_RUN,
				static::DRY_RUN_SHORTCUT,
				InputOption::VALUE_NONE,
				'Should write operations be skipped?'
			)->addArgument(
				static::ARGUMENT_COURT,
				InputArgument::REQUIRED,
				sprintf('Court which is imported, available values are: %s', implode(', ', array_keys(Court::$types)))
			);
	}

	/**
	 * Expects validate directory with data to import.
	 * Note: executed in transaction
	 * @param OutputInterface $consoleOutput
	 * @param string $output Output to be stored
	 * @param int $courtId ID of court
	 * @param bool $dryRun whether the write operations should be performed
	 * @return array where first is number of imported and second number of duplicated items.
	 * @internal param string $directory
	 * @internal param bool $overwrite whether the files should be overwritten
	 */
	public function processCourt(OutputInterface $consoleOutput, &$output, $courtId, $dryRun)
	{
		$court = $this->getCourt($courtId);
		$merged = 0;
		$renamed = 0;
		$ignored = 0;

		$cases = $this->causeService->findFromCourt($court);
		/** @var Cause $case */
		foreach ($cases as $case)
		{
			// Prepare new registry mark (newly normalized)
			$oldRegistryMark = $case->registrySign;
			$newRegistryMark = Normalize::registryMark($case->registrySign);
			if ($newRegistryMark === $case->registrySign) {
				$ignored++;
				continue;
			}
			// Check if there exists with same
			$newCase = $this->causeService->find($newRegistryMark);
			if (!$newCase) { // Just update
				$case->registrySign = $newRegistryMark;
				$this->causeService->save($case);
				$output .= "Renamed case {$oldRegistryMark} to {$newRegistryMark}.\n";
				$consoleOutput->write("Renamed case {$oldRegistryMark} to {$newRegistryMark}.\n");
				$renamed++;
			} else { // Merge
				$merged++;
				// Migrate documents
				/** @var Document $document */
				foreach ($this->documentService->findByCaseId($case->id) as $document) {
					$document->case = $newCase;
					$this->documentService->save($document);
				}
				// Migrate advocates tagging
				/** @var TaggingAdvocate $tagging */
				foreach ($this->taggingService->findAdvocateTaggingsByCase($case) as $tagging) {
					$tagging->case = $newCase;
					$this->taggingService->persist($tagging);
				}
				// Migrate case result tagging
				/** @var TaggingCaseResult $tagging */
				foreach ($this->taggingService->findCaseResultTaggingsByCase($case) as $tagging) {
					$tagging->case = $newCase;
					$this->taggingService->persist($tagging);
				}
				// Remove original entity
				$this->causeService->remove($case);
				$output .= "Merget case {$oldRegistryMark} to {$newRegistryMark}.\n";
				$consoleOutput->write("Merged case {$oldRegistryMark} to {$newRegistryMark}.\n");
			}
		}

		return [$merged, $renamed, $ignored];
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$this->prepare();
		$court = $input->getArgument(static::ARGUMENT_COURT);
		$dryRun = $input->getOption(static::DRY_RUN);
		$code = 0;
		$output = null;
		$message = null;
		if (!isset(Court::$types[$court])) {
			$message = 'Error: The given court is not valid value.';
			$code = static::RETURN_CODE_INVALID_COURT;
			$consoleOutput->writeln($message);
		} else {

			$courtEntity = $this->getCourt(Court::$types[$court]);
			$temp = sprintf("Court: %s\n", $courtEntity->name);
			$output .= $temp;
			$consoleOutput->write($temp);

			// import to db
			list($merged, $renamed, $ignored) = $this->processCourt($consoleOutput, $output, Court::$types[$court], $dryRun);
			if (!$dryRun && ($merged > 1 || $renamed > 0)) {
				$this->causeService->flush();
			}
			if ($dryRun) {
				$message = "Dry run: Renamed {$renamed} cases, merged {$merged} cases and ignored {$ignored}\n";
			} else {
				$message = "Renamed {$renamed} cases, merged {$merged} cases and ignored {$ignored}\n";
			}
			$output .= $message;
			$consoleOutput->write($message);
		}
		$this->finalize($code, $output, $message);
		if ($code !== self::RETURN_CODE_SUCCESS) {
			$consoleOutput->writeln($message);
		}
	}

	private function getCourt($courtId)
	{
		if (!$this->court || $this->court->id != $courtId) {
			$this->court = $this->courtService->getById($courtId);
		}
		return $this->court;
	}
}
