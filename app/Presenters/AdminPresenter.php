<?php

namespace App\Presenters;

use App\Components\LoginForm\LoginFormFactory;
use App\Enums\AdvocateStatus;
use App\Model\Advocates\Advocate;
use App\Model\Advocates\AdvocateInfo;
use App\Model\Orm;
use App\Model\Services\DisputationService;
use App\Model\Services\JobService;
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

	/** @var DisputationService @inject */
	public $disputationService;

	/** @var JobService @inject */
	public $jobService;

	/** @var Orm @inject */
	public $orm;

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
		$this->template->disputations = $this->disputationService->getStatistics();
		$this->template->failedJobRuns = $this->jobService->getFailedInLastTwoWeeks();
	}

	protected function createComponentLoginForm()
	{
		return $this->loginFormFactory->create();
	}

}
