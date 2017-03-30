<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Enums\CaseResult;
use App\Enums\Court;
use App\Enums\TaggingStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Services\AdvocateService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\BootstrapForm;
use App\Utils\Responses\OriginalMimeTypeFileResponse;
use App\Utils\TemplateFilters;
use IPub\VisualPaginator\Components\Control;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Utils\Validators;


class TaggingPresenter extends SecuredPresenter
{
	/** @var CauseService @inject */
	public $causeService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var TaggingService @inject */
	public $taggingService;

	/** @var AdvocateService @inject */
	public $advocateService;

	/** @privilege(App\Utils\Resources::TAGGING, App\Utils\Actions::VIEW) */
	public function actionCase(int $caseId)
	{
		$case = $this->causeService->get($caseId);
		if (!$case) {
			throw new BadRequestException('No such case [{$id}]', 404);
		}
		$documents = $this->documentService->findByCaseId($case->id);

		$this->template->case = $case;
		$this->template->documents = $documents;
		/** @var TaggingCaseResult $caseResult */
		$this->template->caseResult = $caseResult = $this->prepareCasesResults([$case])[$case->id] ?? null;
		$this->template->advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);
		if ($caseResult) {
			/** @var Form $form */
			$form = $this->getComponent('caseResultForm');
			$form->setDefaults([
				'document' => ($caseResult->document) ? $caseResult->document->id : null,
				'case_result' => $caseResult->caseResult,
				'status' => $caseResult->status,
				'is_final' => $caseResult->isFinal,
				'debug' => $caseResult->debug
			]);
		}
	}

	/** @privilege(App\Utils\Resources::TAGGING, App\Utils\Actions::VIEW) */
	public function actionDefault(?string $court = null, ?bool $onlyDisputed = false, ?string $filter = null, ?int $count = null)
	{
		$court = ($court && array_key_exists($court, Court::$types)) ? Court::$types[$court] : null;
		$count = ($count) ? min(100, max($count, 1)) : 100;

		/** @var Control $visualPaginator */
		$visualPaginator = $this->getComponent('visualPaginator');
		$cases = $this->causeService->findForManualTagging($court, $onlyDisputed, $filter);
		$totalCount = $cases->countStored();
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = $count;
		$paginator->itemCount = $totalCount;
		$cases = $cases->limitBy($paginator->itemsPerPage, $paginator->offset);
		$results = $this->prepareCasesResults($cases->fetchAll());
		$advocatesTaggings = $this->prepareAdvocates($cases->fetchAll());

		$this->template->onlyDisputed = $onlyDisputed;
		$this->template->filter = $filter;
		$this->template->court = $court;
		$this->template->paginator = $paginator;
		$this->template->cases = $cases;
		$this->template->results = $results;
		$this->template->advocatesTaggings = $advocatesTaggings;
	}

	/**
	 * Renders given document from local copy (available only to admins)
	 *
	 * @privilege(App\Utils\Resources::TAGGING, App\Utils\Actions::VIEW)
	 * @param int $documentId ID of document to show
	 * @throws BadRequestException when no such document exists
	 * @throws AbortException when redirection happens
	 */
	public function actionDocument(int $documentId)
	{
		$document = $this->documentService->get($documentId);
		if (!$document) {
			throw new BadRequestException('No such document [{$id}]', 404);
		}
		$localCopy = new OriginalMimeTypeFileResponse(__DIR__ . '/../../' . $document->localPath, NULL, NULL, FALSE);
		$this->sendResponse($localCopy);
	}

	private function prepareAdvocates($data)
	{
		$temp = $this->taggingService->findLatestAdvocateTaggingByCases($data);
		$output = [];
		foreach ($temp as $row) {
			$output[$row->case->id] = $row;
		}
		return $output;
	}

	private function prepareCasesResults($data)
	{
		$output = [];
		$temp = $this->taggingService->findCaseResultLatestTaggingByCases($data);
		foreach ($temp as $row) {
			$output[$row->case->id] = $row;
		}
		return $output;
	}

	protected function createComponentVisualPaginator()
	{
		$control = new Control();
		$control->setTemplateFile('bootstrap.latte');
		return $control;
	}

	protected function createComponentCaseResultForm()
	{
		$documents = $this->documentService->findByCaseIdPairs($this->getParameter('caseId'));
		$form = new BootstrapForm();
		$form->addSelect('document', 'Dokument', $documents)
			->setPrompt('Žádný');
		$form->addSelect('case_result', 'Výsledek', CaseResult::$statuses);
		$form->addSelect('status', 'Status', TaggingStatus::$statuses)
			->setDefaultValue(TaggingStatus::STATUS_PROCESSED);
		$form->addCheckbox('is_final', 'Finální')
			->setDefaultValue(true);
		$form->addText('debug', 'Poznámka');
		$form->addSubmit('sent', 'Vložit');
		$form->onError[] = function (Form $form)
		{
			$this->template->showCaseResultForm = true;
		};

		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$tagging = new TaggingCaseResult();
			$tagging->case = $this->causeService->get($this->getParameter('caseId'));
			$tagging->document = $values->document ? $this->documentService->get($values->document) : null;
			$tagging->caseResult = $values->case_result;
			$tagging->status = $values->status;
			$tagging->isFinal = $values->is_final;
			$tagging->debug = $values->debug;
			$tagging->insertedBy = $this->user->getId();

			if ($this->taggingService->persistCaseResultIfDiffers($tagging)) {
				$this->taggingService->flush();
				$this->flashMessage('Nové tagování výsledku bylo úspěšně uloženo.', 'alert-success');
			} else {
				$this->flashMessage('Nové tagování výsledku je stejné jako předchozí, nic nebylo provedeno.', 'alert-warning');
			}
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentAdvocateForm()
	{
		$caseId = $this->getParameter('caseId');
		$documents = $this->documentService->findByCaseIdPairs($caseId);
		$case = $this->causeService->get($caseId);
		/** @var TaggingAdvocate $advocateTagging */
		$advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);

		$form = new BootstrapForm();
		$this->addComponent($form, 'advocateForm');

		$advocates = [];
		$httpData = $form->getHttpData();
		if (($advocateTagging && $advocateTagging->advocate) || ($httpData && isset($httpData['advocate']) && Validators::isNumericInt($httpData['advocate']))) {
			/** @var Advocate $advocate */
			$advocate = $this->advocateService->get(!empty($httpData['advocate']) ? (int) $httpData['advocate'] : $advocateTagging->advocate->id);
			if ($advocate) {
				/** @var AdvocateInfo $currentAdvocateInfo */
				$currentAdvocateInfo = $advocate->advocateInfo->get()->fetch();
				if ($currentAdvocateInfo) {
					$advocates = [$advocate->id => TemplateFilters::formatName($currentAdvocateInfo->name, $currentAdvocateInfo->surname, $currentAdvocateInfo->degreeBefore, $currentAdvocateInfo->degreeAfter, $currentAdvocateInfo->city)];
				}
			}
		}

		$form->addSelect('document', 'Dokument', $documents)
			->setPrompt('Žádný');
		$form->addSelect('advocate', 'Advokát', $advocates)
			->setPrompt(null)
			->setDefaultValue(key($advocates))
			->setHtmlAttribute('class', 'advocate-autocomplete');
		$form->addSelect('status', 'Status', TaggingStatus::$statuses)
			->setDefaultValue(TaggingStatus::STATUS_PROCESSED);
		$form->addCheckbox('is_final', 'Finální')
			->setDefaultValue(true);
		$form->addText('debug', 'Poznámka');
		$form->addSubmit('sent', 'Vložit');

		$form->onError[] = function (Form $form)
		{
			$this->template->showAdvocateForm = true;
		};

		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$tagging = new TaggingAdvocate();
			$tagging->case = $this->causeService->get($this->getParameter('caseId'));
			$tagging->document = $values->document ? $this->documentService->get($values->document) : null;
			$tagging->advocate = $values->advocate ?? null;
			$tagging->status = $values->status;
			$tagging->isFinal = $values->is_final;
			$tagging->debug = $values->debug;
			$tagging->insertedBy = $this->user->getId();

			if ($this->taggingService->persistAdvocateIfDiffers($tagging)) {
				$this->taggingService->flush();
				$this->flashMessage('Nové tagování advokáta bylo úspěšně uloženo.', 'alert-success');
			} else {
				$this->flashMessage('Nové tagování advokáta je stejné jako předchozí, nic nebylo provedeno.', 'alert-warning');
			}
			$this->redirect('this');
		};

		return $form;
	}
}
