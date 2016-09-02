<?php

namespace App\Presenters;

use App\Components\ProfileForm\ProfileFormFactory;
use App\Components\UserForm\UserFormFactory;
use App\Enums\UserType;
use App\Model\Services\JobService;
use App\Model\Services\UserService;
use App\Utils\Resources;
use App\Utils\Actions;
use Nette;


class JobPresenter extends SecuredPresenter
{
	/** @var JobService @inject */
	public $jobService;

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionDefault()
	{
		$this->template->jobs = $this->jobService->findAll();
	}

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionRuns($jobId)
	{
		$this->template->job = $this->jobService->get($jobId);
		$this->template->runs = $this->jobService->findRuns($jobId);
	}

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionRun($runId)
	{
		$this->template->run = $this->jobService->findRun($runId);
	}
}
