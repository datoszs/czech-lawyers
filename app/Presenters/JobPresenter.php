<?php

namespace App\Presenters;

use App\Model\Services\JobService;


class JobPresenter extends SecuredPresenter
{
	/** @var JobService @inject */
	public $jobService;

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionDefault($order = null)
	{
		$this->template->order = $order;
		if (!$order) {
			$this->template->jobs = $this->jobService->findAll();
		} else {
			$this->template->jobs = $this->jobService->findAllSortedByExecuted();
		}
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
