<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
	/** @var Model\Services\CauseService @inject */
	public $causaServices;

	/** @var Model\Services\DocumentService @inject */
	public $documentServices;

	public function actionDefault()
	{
	}
}
