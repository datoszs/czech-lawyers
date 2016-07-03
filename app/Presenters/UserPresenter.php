<?php

namespace App\Presenters;

use App\Components\ProfileForm\ProfileFormFactory;
use App\Components\UserForm\UserFormFactory;
use App\Enums\UserType;
use App\Model\Services\UserService;
use App\Utils\Resources;
use App\Utils\Actions;
use Nette;


class UserPresenter extends SecuredPresenter
{
	/** @var UserService @inject */
	public $userService;

	/** @var UserFormFactory @inject */
	public $userFormFactory;

	/** @var ProfileFormFactory @inject */
	public $profileFormFactory;

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::VIEW) */
	public function actionDefault()
	{
		$this->template->users = $this->userService->findAll();
	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::CREATE) */
	public function actionAdd()
	{

	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::EDIT) */
	public function actionEdit($id)
	{
		$this->template->entity = $entity = $this->userService->get($id);
		if ($entity->type == UserType::TYPE_SYSTEM) {
			$this->flashMessage('Systémové účty nemohou být upravovány skrze administraci.', 'alert-warning');
			$this->redirect('default');
		}
	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::DELETE) */
	public function actionDelete($id)
	{
		$this->template->entity = $entity = $this->userService->get($id);
		if ($entity->type == UserType::TYPE_SYSTEM) {
			$this->flashMessage('Systémové účty nemohou být mazány skrze administraci.', 'alert-warning');
			$this->redirect('default');
		}
	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::DISABLE) */
	public function handleDisable($id)
	{
		$entity = $this->userService->get($id);
		if ($entity->type == UserType::TYPE_SYSTEM) {
			$this->flashMessage('Systémové účty nemohou být měněny skrze administraci.', 'alert-warning');
			$this->redirect('default');
		}
		$this->userService->disable($id);
	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::ENABLE) */
	public function handleEnable($id)
	{
		$entity = $this->userService->get($id);
		if ($entity->type == UserType::TYPE_SYSTEM) {
			$this->flashMessage('Systémové účty nemohou být měněny skrze administraci.', 'alert-warning');
			$this->redirect('default');
		}
		$this->userService->enable($id);
	}

	/** @privilege(App\Utils\Resources::USERS, App\Utils\Actions::PROFILE) */
	public function actionProfile()
	{

	}

	public function createComponentAddForm()
	{
		return $this->userFormFactory->create();
	}

	public function createComponentEditForm()
	{
		return $this->userFormFactory->create($this->getParameter('id'));
	}

	public function createComponentDeleteForm()
	{
		$component = $this->userFormFactory->create($this->getParameter('id'));
		$component->setDeletionMode();
		return $component;
	}

	public function createComponentProfileForm()
	{
		return $this->profileFormFactory->create();
	}

}
