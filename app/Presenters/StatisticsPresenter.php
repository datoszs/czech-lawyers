<?php declare(strict_types=1);
namespace App\Presenters;

use App\Model\Services\StatisticsService;

class StatisticsPresenter extends SecuredPresenter
{
	/** @var StatisticsService @inject */
	public $statisticsService;

	/** @privilege(App\Utils\Resources::STATISTICS, App\Utils\Actions::VIEW) */
	public function actionDefault()
	{
		$this->template->caseCount = $this->statisticsService->caseCount();
		$this->template->caseCountPerYear = $this->statisticsService->caseCountPerYear();

		$this->template->officialDataCount = $this->statisticsService->officialDataCount();
		$this->template->officialDataCountPerYear = $this->statisticsService->officialDataPerYearCount();

		$this->template->countLatestAdvocateTaggingsByStatus = $this->statisticsService->countLatestAdvocateTaggingsByStatus();
		$this->template->countLatestCaseResultTaggingsByStatus = $this->statisticsService->countLatestCaseResultTaggingsByStatus();

		$this->template->countAdvocates = $this->statisticsService->countAdvocates();

		$this->template->countDocuments = $this->statisticsService->countDocuments();
		$this->template->countDocumentsPerYear = $this->statisticsService->countDocumentsPerYear();
	}

}
