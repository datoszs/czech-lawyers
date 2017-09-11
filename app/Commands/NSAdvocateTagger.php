<?php
declare(strict_types=1);

namespace App\Commands;

use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ITransactionLogger;
use App\Enums\TaggingStatus;
use App\Exceptions\ExtractionException;
use App\Exceptions\MultipleMatchesException;
use App\Exceptions\NoMatchException;
use App\Extractors\NSAdvocateExtractor;
use App\Model\Advocates\Advocate;
use App\Model\Cause\Cause;
use App\Model\Documents\Document;
use App\Model\Services\AdvocateService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Utils\AdvocateMatcher;
use App\Utils\AdvocatePrefixPrematcher;
use App\Utils\Normalize;
use App\Utils\TemplateFilters;
use Nette\Utils\Strings;
use Nextras\Dbal\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class NSAdvocateTagger extends AdvocateTagger
{

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var Connection @inject */
	public $connection;

	/** @var NSAdvocateExtractor */
	private $extractor;

	/** @var AdvocateMatcher */
	private $matcher;

	/** @var AdvocatePrefixPrematcher */
	private $prematcher;

	protected function configure()
	{
		$this->setName('app:ns-advocate-tagger')
			->setDescription('Tag advocates for cases from Supreme Court (only non-final and differing).');
	}

	protected function beforeExecute()
	{
		$this->court = $this->courtService->getNS();
		$this->extractor = new NSAdvocateExtractor();
		$this->matcher = $this->prepareAdvocateMatcher();
		$this->prematcher = new AdvocatePrefixPrematcher();
	}

	private function prepareAdvocateMatcher()
	{
		$advocates = $this->connection
			->query('SELECT concat_ws(\' \', name, surname) AS fullname, string_agg(DISTINCT advocate_id::text, \' \') AS advocate_id FROM advocate_info GROUP BY name, surname')
			->fetchPairs('fullname', 'advocate_id');

		return new AdvocateMatcher($advocates);
	}

	private function prepareTagging(Cause $cause, $debug, Document $document = null, Advocate $advocate = null)
	{
		$tagging = new TaggingAdvocate();
		$tagging->isFinal = false;
		$tagging->insertedBy = $this->user;
		$tagging->debug = $debug;
		$tagging->case = $cause;
		if ($advocate) {
			$tagging->advocate = $advocate;
			$tagging->status = TaggingStatus::STATUS_PROCESSED;
		} else {
			$tagging->status = TaggingStatus::STATUS_FAILED;
		}
		if ($document) {
			$tagging->document = $document;
		}
		$tagging->jobRun = $this->jobRun;
		return $tagging;
	}

	private function isTDO(Cause $cause)
	{
		return Strings::contains($cause->registrySign, ' tdo ');
	}

	private function tagToAdvocateName(Cause $cause, string $advocateName, string &$output, OutputInterface $consoleOutput, String $originalAdvocateName, Document $document = null, ITransactionLogger $transactionLogger) {
		try {
			list($advocateNameNominativ, $advocateId) = $this->matcher->match($advocateName);
		} catch (NoMatchException $ex) {
			$temp = sprintf("Case [%s] file [%s] no match for [%s].\n", TemplateFilters::formatRegistryMark($cause->registrySign), $document->localPath ?? null, $advocateName);
			$output .= $temp;
			$consoleOutput->write($temp);
			// we found advocate, but we could not match it with db, further procession could provide bogus result.
			return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, sprintf('Advocate extracted: %s no match in our database.', $advocateName)));
		} catch (MultipleMatchesException $ex) {
			$temp = sprintf("Case [%s] file [%s] multiple matches for [%s]: [%s].\n", TemplateFilters::formatRegistryMark($cause->registrySign), $document->localPath ?? null, $advocateName, $ex->getMessage());
			$output .= $temp;
			$consoleOutput->write($temp);
			// we found advocate, but we could not match it with db, further procession could provide bogus result.
			return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, sprintf('Advocate extracted: %s but too many matches %s.', $advocateName, $ex->getMessage())));
		}
		$advocateTagging = $this->prepareTagging($cause, $advocateName, $document, $this->advocateService->get($advocateId));
		$result = $this->taggingService->persistAdvocateIfDiffers($advocateTagging);
		if ($result) {
			$transactionLogger->logCreate(AuditedSubject::CASE_TAGGING, "Create new advocate tagging with ID [{$advocateTagging->id}] of case [{$advocateTagging->case->registrySign}] to advocate [{$advocateNameNominativ}] with ID [{$advocateId}]. Note [{$advocateTagging->debug}].", AuditedReason::SCHEDULED);
			$temp = sprintf("Case [%s] file [%s] tagged with [%s -> %s].\n", TemplateFilters::formatRegistryMark($cause->registrySign), $document->localPath ?? null, (($originalAdvocateName !== $advocateName) ? $originalAdvocateName . '->' . $advocateName : $advocateName), $advocateNameNominativ);
			$output .= $temp;
			$consoleOutput->write($temp);
		}
		return $result;
	}

	private function prepareCaseAdvocates(Cause $cause) : array
	{
		$caseAdvocates = array_unique(array_column($cause->officialData, 'fullname'));
		// Swap names as NS sends data in different order: JUDr. Sokol Tomáš
		foreach ($caseAdvocates as &$advocate) {
			$advocate = Normalize::fixSurnameName($advocate);
		}
		return $caseAdvocates;
	}

	protected function processCase(Cause $cause, string &$output, OutputInterface $consoleOutput, ITransactionLogger $transactionLogger)
	{
		$caseAdvocates = [];
		if ($cause->officialData) { // First process official data.
			$caseAdvocates = $this->prepareCaseAdvocates($cause);
		}
		// For TDO with exactly one advocate we can create tagging directly
		if ($cause->officialData && $this->isTDO($cause)) {
			if (count($caseAdvocates) === 1) {
				return $this->tagToAdvocateName($cause, $caseAdvocates[0], $output, $consoleOutput, $caseAdvocates[0], null, $transactionLogger);
			} else {
				return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, sprintf('TDO with multiple advocates [%s].', implode(', ', $caseAdvocates))));
			}
		}

		// There could be tagging if exactly one distinct advocate is given in official data...
		// But it seems that this produces invalid tagging as data from court provides incomplete data (for example when it is authorized person instead of lawyer).

		$documents = $this->documentService->findByCaseId($cause->id);
		// Iterate through all documents before the extractor succeed, expects descending order!
		/** @var Document $document */
		foreach ($documents as $document) {
			$completePath = __DIR__ . '/../../' . $document->localPath;
			if (!file_exists($completePath)) {
				$temp = sprintf("File [%s] is missing, skipping case [%s].\n", $document->localPath, TemplateFilters::formatRegistryMark($cause->registrySign));
				$output .= $temp;
				$consoleOutput->write($temp);
				return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, sprintf('Missing file: %s', $document->localPath)));
			}
			try {
				$advocateName = $this->extractor->extract($completePath);
			} catch (ExtractionException $exception) {
				$temp = sprintf("Case [%s] file [%s] extraction error [%s].\n", TemplateFilters::formatRegistryMark($cause->registrySign), $document->localPath, $exception->getMessage());
				$output .= $temp;
				$consoleOutput->write($temp);
				continue;
			}
			// Attempt to find advocate in case advocates by prefix match)
			$originalAdvocateName = $advocateName;
			$advocateName = $this->prematcher->prefixMatch($advocateName, $caseAdvocates);

			return $this->tagToAdvocateName($cause, $advocateName, $output, $consoleOutput, $originalAdvocateName, $document, $transactionLogger);
		}
		$documentsCount = count($documents);
		if ($documentsCount === 0) {
			return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, 'No documents.'));
		} else {
			return $this->taggingService->persistAdvocateIfDiffers($this->prepareTagging($cause, sprintf('Could not parse any advocate from [%s] documents.', $documentsCount)));
		}
	}
}
