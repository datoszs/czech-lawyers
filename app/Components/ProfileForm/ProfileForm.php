<?php
namespace App\Components\ProfileForm;


use App\Model\Services\UserService;
use App\Model\Users\User as UserEntity;
use App\Utils\BootstrapForm;
use App\Utils\Templated;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Security\User;

class ProfileForm extends Control
{
	use Templated;

	/** @var User */
	private $user;

	/** @var UserService */
	private $userService;

	public function __construct(User $user, UserService $userService)
	{
		parent::__construct();
		$this->user = $user;
		$this->userService = $userService;
	}

	public function createComponentForm()
	{
		$form = new BootstrapForm();
		$form->addGroup('Změna hesla');
		$password = $form->addPassword('password', 'Heslo');
		$password2 = $form->addPassword('password2', 'Heslo znovu');

			$password->setRequired('Zadejte, prosím, heslo.');
			$password2->setRequired(('Vyplňte, prosím, heslo pro kontrolu shody.'))
				->addCondition(Form::FILLED)
				->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['password']);

		$form->setCurrentGroup(null);
		$form->addSubmit('sent', 'Změnit heslo');
		

		$form->onSuccess[] = function (Form $form) {
			$values = $form->getValues();
			/** @var UserEntity $entity */
			$entity = $this->userService->get($this->user->getId());
			$entity->password = Passwords::hash($values->password);
			$this->userService->save($entity);
			$this->getPresenter()->flashMessage('Vaše heslo bylo úspěšně změněno.', 'alert-success');
			$this->redirect('this');
		};
		return $form;
	}
}