<?php declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining download link to exported data
 *
 * @ApiRoute(
 *     "/api/download-export/",
 *     section="Export",
 * )
 */
class DownloadExportPresenter extends Presenter
{

	/** @var ILogger @inject */
	public $auditing;

	/**
	 * Get link to current version of exported data
	 *
	 * <json>
	 *     {
	 *         "link": "https://www.example.com/data/sY345i"
	 *     }
	 * </json>
	 *
	 * @ApiRoute(
	 *     "/api/download-export",
	 *     section="Export",
	 *     presenter="API:DownloadExport",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws AbortException when redirection happens
	 */
	public function actionRead() : void
	{
		$dumpIdentification = 'foo';
		$dumpTimestamp = '2017-10-01_10-10-10';
		$this->auditing->logAccess(AuditedSubject::DATA_EXPORT, "Access data export [{$dumpIdentification}] from [{$dumpTimestamp}].", AuditedReason::REQUESTED_EXPORT);
		$this->sendJson([
			'link' => 'https://www.example.com/data/sY345i'
		]);
	}
}
