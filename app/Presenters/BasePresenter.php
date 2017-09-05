<?php

namespace App\Presenters;

use App\Auditing\ILogger;
use Nette;
use Nette\Application\ForbiddenRequestException;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var ILogger @inject */
	public $auditing;

	public function requirePrivilege($resource, $action = NULL)
	{
		if (!$this->user->isAllowed($resource, $action)) {
			$this->requireLogin();
			throw new ForbiddenRequestException('Not authorized.');
		}
	}

	public function requireLogin()
	{
		if (!$this->user->isLoggedIn()) {
			$storedRequest = $this->storeRequest();
			$this->redirect('Admin:login', array('backlink' => $storedRequest));
		}
	}
}
