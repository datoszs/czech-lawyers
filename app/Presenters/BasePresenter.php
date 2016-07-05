<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Permission;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
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
