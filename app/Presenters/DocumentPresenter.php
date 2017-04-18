<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\Services\DocumentService;
use App\Utils\Responses\OriginalMimeTypeFileResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

class DocumentPresenter extends SecuredPresenter
{

	/** @var DocumentService @inject */
	public $documentService;

	/**
	 * Renders given document from local copy (available only to admins)
	 *
	 * @privilege(App\Utils\Resources::DOCUMENTS, App\Utils\Actions::VIEW_PUBLIC)
	 * @param int $id ID of document to show
	 * @throws BadRequestException when no such document exists
	 * @throws AbortException when redirection happens
	 */
	public function actionView(int $id)
	{
		$document = $this->documentService->get($id);
		if (!$document) {
			throw new BadRequestException('No such document [{$id}]', 404);
		}
		$localCopy = new OriginalMimeTypeFileResponse(__DIR__ . '/../../' . $document->localPath, NULL, NULL, FALSE);
		$this->sendResponse($localCopy);
	}
}
