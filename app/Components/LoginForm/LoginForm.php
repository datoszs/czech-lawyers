<?php
namespace App\Components\LoginForm;


use App\Model\Services\UserService;
use App\Utils\BootstrapForm;
use App\Utils\Templated;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\User;

class LoginForm extends Control
{
	use Templated;

	/** @var User */
	private $user;

	public function __construct(User $user)
	{
		parent::__construct();
		$this->user = $user;
	}

	public function createComponentForm()
	{
		$form = new BootstrapForm();
		$form->addText('username', 'Jméno')
			->setRequired('Zadejte, prosím, přihlašovací jméno.');
		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte, prosím, heslo.');
		$form->addSubmit('sent', 'Přihlásit');
		

		$form->onSuccess[] = function (Form $form) {
			$values = $form->getValues();
			try {
				$this->user->login($values->username, $values->password);
			} catch (AuthenticationException $ex) {
				if ($ex->getCode() == IAuthenticator::IDENTITY_NOT_FOUND) {
					$form->addError('Uživatel s tímto přihlašovacím jménem neexistuje.');
				} elseif ($ex->getCode() == IAuthenticator::INVALID_CREDENTIAL) {
					$form->addError('Špatné heslo pro tento uživatelský účet.');
				} elseif ($ex->getCode() == UserService::DISABLED_ACCOUNT) {
					$form->addError('Tento uživatelský účet je zakázaný (neaktivní).');
				} elseif ($ex->getCode() == UserService::DISABLED_LOGIN) {
					$form->addError('Tomuto uživatelskému účtu bylo zakázáno přihlášení.');
				} else {
					$form->addError('Přihlášení se nezdařilo. Kontaktujte správce.');
				}
			}
			if (isset($this->getPresenter()->backlink) && $this->getPresenter()->backlink) {
				$this->getPresenter()->restoreRequest($this->getPresenter()->backlink);
			} else {
				$this->getPresenter()->redirect('Admin:');
			}
		};
		return $form;
	}
}