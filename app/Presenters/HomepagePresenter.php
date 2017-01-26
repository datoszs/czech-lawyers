<?php

namespace App\Presenters;

use App\Utils\BootstrapForm;
use App\Model;
use Nette\Application\UI\Form;


class HomepagePresenter extends BasePresenter
{
	/** @var Model\Services\CauseService @inject */
	public $causaServices;

	/** @var Model\Services\DocumentService @inject */
	public $documentServices;

	public function actionDefault()
	{
	}

	public function createComponentSearchForm()
	{
		$form = new BootstrapForm();
		$form->setType(BootstrapForm::VERTICAL);
		$form->addText('query', 'Jméno / IČ')
			->setRequired('Zadejte, prosím, jméno nebo IČ advokáta.')
			->setDefaultValue($this->getParameter('query'));
		$form->addSubmit('searched', 'Hledat')
			->setHtmlAttribute('class', 'btn-lg btn-success');
		$form->onSuccess[] = function (Form $form)
		{
			$values = $form->getValues();
			$this->redirect('Advocate:search', $values['query']);
		};
		return $form;
	}
}
