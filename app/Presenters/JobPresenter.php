<?php

namespace App\Presenters;

use App\Model\Services\JobService;
use App\Utils\Responses\OriginalMimeTypeFileResponse;
use IPub\VisualPaginator\Components\Control;


class JobPresenter extends SecuredPresenter
{

	const JOB_RUN_LOGS_DIR = __DIR__ . '/../../storage/logs/job_run/';

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
	public function actionFailedRuns()
	{
		$runs = $this->jobService->findFailedRuns();
		$visualPaginator = $this->getComponent('visualPaginator');
		$totalCount = $runs->countStored();
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 20;
		$paginator->itemCount = $totalCount;
		$runs = $runs->limitBy($paginator->itemsPerPage, $paginator->offset);

		$this->template->runs = $runs;
	}

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionRun($runId)
	{
		$this->template->run = $this->jobService->findRun($runId);
		$filename = static::JOB_RUN_LOGS_DIR . $runId . '.bz';
		$this->template->hasLogFile = file_exists($filename);
	}

	/** @privilege(App\Utils\Resources::JOBS, App\Utils\Actions::VIEW) */
	public function actionLog($runId)
	{
		$filename = static::JOB_RUN_LOGS_DIR . $runId . '.bz';
		$this->sendResponse(new OriginalMimeTypeFileResponse($filename));
	}

	protected function createComponentVisualPaginator()
	{
		$control = new Control();
		$control->setTemplateFile(__DIR__ . '/templates/pagination.latte');
		return $control;
	}
}
