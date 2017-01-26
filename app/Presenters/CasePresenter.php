<?php

namespace App\Presenters;

use App\Model\Services\AdvocateService;
use App\Model\Services\CauseService;
use App\Model\Services\DocumentService;
use App\Model\Services\TaggingService;
use App\Utils\BootstrapForm;
use App\Utils\Normalize;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


class CasePresenter extends BasePresenter
{
	/** @var CauseService @inject */
	public $causeService;

	/** @var DocumentService @inject */
	public $documentService;

	/** @var TaggingService @inject */
	public $taggingService;

	public function actionSearch($query = null, $match = null)
	{
		$data = [];
		if ($query) {
			$data = $this->causeService->search(Normalize::registryMark($query), 101, $match);
		}
		$this->template->query = $query;
		$this->template->cases = $data;
		$this->template->results = $this->prepareCasesResults($data);
		$this->template->advocatesTaggings = $this->prepareAdvocates($data);
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

	public function actionDetail($id, $slug = null)
	{
		$case = $this->causeService->get($id);
		if (!$case) {
			throw new BadRequestException('No such case [{$id}]', 404);
		}
		$documents = $this->documentService->findByCaseId($case->id);

		$this->template->case = $case;
		$this->template->documents = $documents;
		$this->template->results = $this->prepareCasesResults([$case]);
		$this->template->advocateTagging = $this->taggingService->getLatestAdvocateTaggingFor($case);
	}

	public function createComponentSearchForm()
	{
		$form = new BootstrapForm();
		$form->setType(BootstrapForm::VERTICAL);
		$form->addText('query', 'Spisová (senátní) značka')
			->setRequired('Zadejte, prosím, spisovou (senátní) značku.')
			->setDefaultValue($this->getParameter('query'));
		$form->addSelect('match', 'Shoda', [null => 'Kdekoliv', 'start' => 'Od začátku', 'end' => 'Od konce'])
			->setDefaultValue($this->getParameter('match'));
		$form->addSubmit('searched', 'Hledat');
		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$this->redirect('search', $values['query'], $values['match']);
		};
		return $form;
	}
}
