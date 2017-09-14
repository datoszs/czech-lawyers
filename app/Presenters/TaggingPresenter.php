<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Enums\CaseResult;
use App\Enums\Court;
use App\Enums\TaggingStatus;
use App\Exceptions\NoSuchDisputeException;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Cause\Cause;
use App\Model\Services\AdvocateService;
use App\Model\Services\CauseService;
use App\Model\Services\DisputationService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Model\Taggings\TaggingAdvocate;
use App\Model\Taggings\TaggingCaseResult;
use App\Utils\BootstrapForm;
use App\Utils\MailService;
use App\Utils\Normalize;
use IPub\VisualPaginator\Components\Control;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Mail\SendException;
use Nette\Utils\Validators;
use Nextras\Orm\Mapper\Dbal\DbalCollection;


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

	/** @var DisputationService @inject */
	public $disputationService;

	/** @var MailService @inject */
	public $mailService;

	/** @var DbalCollection */
	private $disputes;

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
		$this->template->disputes = $this->disputes = $this->disputationService->findByCase($case);
		/** @var TaggingCaseResult $caseResult */
		$this->template->caseResult = $caseResult = $this->prepareCasesResults([$case])[$case->id] ?? null;
		$this->template->advocateTagging = $advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);
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
		$caseResultCaseResult = $caseResult->caseResult ?? null;
		$caseResultStatus = $caseResult->status ?? null;
		$caseResultId = $caseResult->id ?? null;

		// Auditing
		if (!$advocateTagging || !$advocateTagging->advocate) {
			return;
		}
		$advocateName = $advocateTagging->advocate ? $advocateTagging->advocate->getCurrentName() : null;
		$advocateId = $advocateTagging->advocate ? $advocateTagging->advocate->id : null;
		$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocateName}] with ID [{$advocateId}].", AuditedReason::REQUESTED_BATCH);
		$this->auditing->logAccess(AuditedSubject::CASE_TAGGING, "Load advocate tagging with ID [{$advocateTagging->id}] of case [{$advocateTagging->case->registrySign}] and advocate [{$advocateName}] with ID [{$advocateId}] together with result [{$caseResultCaseResult} - {$caseResultStatus}] with ID [{$caseResultId}].", AuditedReason::REQUESTED_BATCH);
	}

	/** @privilege(App\Utils\Resources::TAGGING, App\Utils\Actions::VIEW) */
	public function actionDefault(?string $court = null, ?string $registryMark = null, ?string $result = null, ?string $advocate = null)
	{
		$court = ($court && array_key_exists($court, Court::$types)) ? Court::$types[$court] : null;
		$count = 100;
		$registryMarkNormalized = $registryMark !== null ? Normalize::registryMark($registryMark) : null;

		/** @var Control $visualPaginator */
		$visualPaginator = $this->getComponent('visualPaginator');
		$cases = $this->causeService->findForManualTagging($court, $registryMarkNormalized, $advocate ?? 'any', $result ?? 'any');
		$totalCount = $cases->countStored();
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = $count;
		$paginator->itemCount = $totalCount;
		$cases = $cases->limitBy($paginator->itemsPerPage, $paginator->offset);
		$results = $this->prepareCasesResults($cases->fetchAll());
		$advocatesTaggings = $this->prepareAdvocates($cases->fetchAll());
		$disputations = $this->disputationService->findDisputationCounts(array_map(function (Cause $cause) { return $cause->id; }, $cases->fetchAll()));

		$this->template->registryMark = $registryMark;
		$this->template->advocate = $advocate;
		$this->template->result = $result;
		$this->template->court = $court;

		$this->template->paginator = $paginator;
		$this->template->cases = $cases;
		$this->template->results = $results;
		$this->template->advocatesTaggings = $advocatesTaggings;
		$this->template->disputations = $disputations;

		// Auditing
		/** @var TaggingAdvocate $advocateTagging */
		foreach ($advocatesTaggings as $advocateTagging) {
			if (!$advocateTagging->advocate) {
				continue;
			}
			$caseResult = $results[$advocateTagging->case->id] ?? null;
			$caseResultCaseResult = $caseResult->caseResult ?? null;
			$caseResultStatus = $caseResult->status ?? null;
			$caseResultId = $caseResult->id ?? null;
			$advocateName = $advocateTagging->advocate ? $advocateTagging->advocate->getCurrentName() : null;
			$advocateId = $advocateTagging->advocate ? $advocateTagging->advocate->id : null;
			$this->auditing->logAccess(AuditedSubject::ADVOCATE_INFO, "Load advocate [{$advocateName}] with ID [{$advocateId}].", AuditedReason::REQUESTED_BATCH);
			$this->auditing->logAccess(AuditedSubject::CASE_TAGGING, "Load advocate tagging with ID [{$advocateTagging->id}] of case [{$advocateTagging->case->registrySign}] and advocate [{$advocateName}] with ID [{$advocateId}] together with result [{$caseResultCaseResult} - {$caseResultStatus}] with ID [{$caseResultId}].", AuditedReason::REQUESTED_BATCH);
		}
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
		$control->setTemplateFile(__DIR__ . '/templates/pagination.latte');
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
				// TODO
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

		// Auditing
		if ($advocateTagging && $advocateTagging->advocate) {
			$advocateName = $advocateTagging->advocate ? $advocateTagging->advocate->getCurrentName() : null;
			$advocateId = $advocateTagging->advocate ? $advocateTagging->advocate->id : null;
			$this->auditing->logAccess(AuditedSubject::CASE_TAGGING, "Load advocate tagging with ID [{$advocateTagging->id}] of case [{$advocateTagging->case->registrySign}] and advocate [{$advocateName}] with ID [{$advocateId}].", AuditedReason::REQUESTED_INDIVIDUAL);
		}

		// Form creation
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
					$advocates = [$advocate->id => $advocate->getCurrentName()];
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

				// Auditing
				$advocateName = $tagging->advocate ? $tagging->advocate->getCurrentName() : null;
				$advocateId = $tagging->advocate ? $tagging->advocate->id : null;
				$this->auditing->logCreate(AuditedSubject::CASE_TAGGING, "Create new advocate tagging with ID [{$tagging->id}] of case [{$tagging->case->registrySign}] to advocate [{$advocateName}] with ID [{$advocateId}]. Note [{$tagging->debug}].", AuditedReason::FIXUP);
			} else {
				$this->flashMessage('Nové tagování advokáta je stejné jako předchozí, nic nebylo provedeno.', 'alert-warning');
			}
			$this->redirect('this');
		};

		return $form;
	}

	public function createComponentResolveDisputes()
	{
		$form = new BootstrapForm();
		$this->addComponent($form, 'resolveDisputes');
		$form->action .= '#disputations';
		$allowed = [];
		foreach ($this->disputes->fetchAll() as $dispute) {
			$allowed[$dispute->id] = $dispute->id;
		}
		$form->addCheckboxList('responding', 'Odpovědět', $allowed)
			->setRequired('Vyberte, prosím, alespoň jedno rozporování pro odpovídání.');

		$form->addTextArea('response', 'Odpověď', null, 10)
			->setRequired('Vyplňte, prosím, vaši odpověď na vybrané zprávy.');
		$form->addSubmit('sent', 'Odeslat');
		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$disputes = $values->responding;
			foreach ($disputes as $disputeId) {
				try {
					$dispute = $this->disputationService->resolve($disputeId, $values->response, $this->user->id);
				} catch (NoSuchDisputeException $ex) {
					$form->addError('Neplatný výběr odpovídaných rozporování.');
					return;
				}
				$message = $this->mailService->createMessage('disputation-response', [
					'case' => $dispute->case,
					'content' => $values->response
				]);
				$message->setSubject('[Čeští advokáti]: prověření případu');
				$message->addTo($dispute->email, $dispute->fullname);
				$message->setFrom($this->mailService->getNoReplyAddress());
				try {
					$this->mailService->send($message);
				} catch (SendException $exception) {
					$form->addError(sprintf('Nepodařilo se odeslat e-mail pro %s.', $dispute->email));
					return;
				}
			}
			$this->disputationService->flush();
			$this->flashMessage('Odpovědi byly uloženy a odeslány.', 'alert-success');
			$this->redirect('this#top');
		};
		return $form;
	}
}
