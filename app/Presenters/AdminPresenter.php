<?php

namespace App\Presenters;

use App\Components\LoginForm\LoginFormFactory;
use App\Model\Services\UserService;
use Nette;
use App\Utils\Resources;
use App\Utils\Actions;


class AdminPresenter extends SecuredPresenter
{
	/** @var LoginFormFactory @inject */
	public $loginFormFactory;

	/** @var UserService @inject */
	public $userService;

	/** @persistent */
	public $backlink = '';

	/** @privilege(App\Utils\Resources::SHARED, App\Utils\Actions::LOGIN) */
	public function actionLogin()
	{
		if ($this->user->isLoggedIn()) {
			$this->redirect('default');
		}
	}

	/** @privilege(App\Utils\Resources::SHARED, App\Utils\Actions::LOGOUT) */
	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->redirect('login');
	}

	/** @privilege(App\Utils\Resources::SHARED, App\Utils\Actions::VIEW) */
	public function actionDefault()
	{

	}

	protected function createComponentLoginForm()
	{
		return $this->loginFormFactory->create();
	}

}
