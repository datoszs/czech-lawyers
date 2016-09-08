<?php
namespace App\Components\UserForm;


use App\Enums\UserRole;
use App\Enums\UserType;
use App\Model\Services\UserService;
use App\Model\Users\User;
use App\Utils\BaseControl;
use App\Utils\BootstrapForm;
use App\Utils\Normalize;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Security\Passwords;

class UserForm extends BaseControl
{

	/** @var UserService */
	private $userService;

	/** @var int */
	private $id;

	/** @var bool */
	private $deletionMode = false;

	public function __construct($id = null, UserService $userService)
	{
		parent::__construct();
		$this->id = $id;

		$this->userService = $userService;
	}

	public function setDeletionMode()
	{
		$this->deletionMode = true;
	}

	public function render()
	{
		if ($this->deletionMode) {
			$this->setView('confirm');
		}
		if ($this->id) {
			$entity = $this->userService->get($this->id);
			/** @var BootstrapForm $form */
			$form = $this->getComponent('form');
			$form->setDefaults($entity->toArray());

			if ($entity->type === UserType::TYPE_SYSTEM) {
				$form->disable();
			}
		}
		parent::render();
	}

	public function createComponentForm()
	{
		$form = new BootstrapForm();

		$form->addGroup('Přilašovací údaje');
		$form->addText('username', 'Přihlašovací jméno')
			->setRequired('Zadejte, prosím, přihlašovací jméno nového uživatele.')
			->addRule(Form::PATTERN, 'Povolené jsou pouze malá písmena, čísla, spojovník, tečku, zavináč a podtržítko o alespoň jednom znaku.', '[a-z0-9_\-\.@]{1,}')
			->addRule(function (TextInput $input) {
				$value = $input->getValue();
				$entity = $this->userService->findByUsername($value);
				return $entity === NULL || $entity->id == $this->id;
			}, 'Toto přihlašovací jméno již je použito.');
		$password = $form->addPassword('password', 'Heslo');
		$password2 = $form->addPassword('password2', 'Heslo znovu');

		if (!$this->id) {
			$password->setRequired('Zadejte, prosím, heslo.');
			$password2->setRequired(('Vyplňte, prosím, heslo pro kontrolu shody.'))
				->addCondition(Form::FILLED)
					->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['password']);
		} else {
			$password->setOption('description', 'Pouze pokud se mění');
			$password
				->addCondition(Form::FILLED)
					->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['password2']);
			$password2
				->addCondition(Form::FILLED)
					->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['password']);
		}



		$form->addGroup('Servisní informace');
		$form->addSelect('type', 'Typ', UserType::$statuses)
			->setPrompt('-- Vyberte --')
			->setRequired('Vyberte, prosím, typ uživatele.');
		$form->addSelect('role', 'Role', UserRole::$statuses)
			->setPrompt('-- Vyberte --')
			->setRequired('Vyberte, prosím, roli uživatele.');

		$form->addCheckbox('isActive', 'Aktivní')
			->setDefaultValue(true);
		$form->addCheckbox('isLoginAllowed', 'Povoleno přihlášení')
			->setDefaultValue(true);

		$form->setCurrentGroup(null);

		if (!$this->id) {
			$form->addSubmit('sent', 'Přidat');
		} else {
			$form->addSubmit('sent', 'Uložit');
		}
		

		$form->onSuccess[] = function (Form $form) {
			$this->processForm($form);
		};
		return $form;
	}
	public function processForm(Form $form)
	{
		$values = $form->getValues();
		if ($this->id) {
			$user = $this->userService->get($this->id);
		} else {
			$user = new User();
		}
		if ($values->type === UserType::TYPE_SYSTEM) {
			$form['type']->addError('Systémové účty nemohou být spravovány skrze administraci.');
			return;
		}
		$user->username = Normalize::username($values->username);
		if (isset($values->password) && $values->password) {
			$user->password = Passwords::hash($values->password);
		}
		$user->isActive = $values->isActive;
		$user->isLoginAllowed = $values->isLoginAllowed;
		$user->type = $values->type;
		$user->role = $values->role;
		
		$this->userService->save($user);
		if ($this->id) {
			$this->getPresenter()->flashMessage('Uživatel byl úspěšně upraven.', 'alert-success');
		} else {
			$this->getPresenter()->flashMessage('Uživatel byl úspěšně vytvořen.', 'alert-success');
		}
		$this->presenter->redirect('User:default');
	}

	public function createComponentConfirmForm()
	{
		$form = new BootstrapForm();
		$form->addSubmit('delete', 'Smazat');
		$form->addSubmit('cancel', 'Zrušit');

		$form->onSuccess[] = function (Form $form) {
			if ($form['delete']->isSubmittedBy()) {
				$this->userService->delete($this->id);
				$this->getPresenter()->flashMessage('Uživatel byl úspěšně smazán.', 'alert-success');
			} else {
				$this->getPresenter()->flashMessage('Uživatel byl ponechán.', 'alert-info');
			}
			$this->presenter->redirect('User:default');
		};

		return $form;
	}
}