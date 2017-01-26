<?php

namespace App\Presenters;

use App\Model\Services\AdvocateService;
use App\Model\Services\TaggingService;
use App\Utils\BootstrapForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


class AdvocatePresenter extends BasePresenter
{
	/** @var AdvocateService @inject */
	public $advocateService;

	/** @var TaggingService @inject */
	public $taggingService;

	public function actionSearch($query = null)
	{
		$data = [];
		if ($query) {
			$data = $this->advocateService->search($query, 101);
		}
		$this->template->query = $query;
		$this->template->advocates = $data;
	}

	public function actionDetail($id, $slug = null)
	{
		$advocate = $this->advocateService->get($id);
		if (!$advocate) {
			throw new BadRequestException('No such advocate [{$id}]', 404);
		}
		$this->template->advocate = $advocate;
		$this->template->cases = $cases = $this->taggingService->findLatestTaggingByAdvocate([$advocate]);
		$this->template->results = $this->prepareCasesResults($cases);
		$this->template->statistics = $this->taggingService->computeAdvocateStatistics($advocate);
	}

	private function prepareCasesResults($data)
	{
		$data = array_map(function ($row) { return $row->case; }, $data);
		$output = [];
		$temp = $this->taggingService->findCaseResultLatestTaggingByCases($data);
		foreach ($temp as $row) {
			$output[$row->case->id] = $row;
		}
		return $output;
	}

	public function createComponentSearchForm()
	{
		$form = new BootstrapForm();
		$form->setType(BootstrapForm::VERTICAL);
		$form->addText('query', 'Jméno / IČ')
			->setRequired('Zadejte, prosím, jméno nebo IČ advokáta.')
			->setDefaultValue($this->getParameter('query'));
		$form->addSubmit('searched', 'Hledat');
		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$this->redirect('search', $values['query']);
		};
		return $form;
	}
}
