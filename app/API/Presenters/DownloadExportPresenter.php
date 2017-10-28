<?php declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use App\Utils\XSendFileResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;
use Tracy\ILogger as ILoggerTracy;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for downloading current exported data.
 *
 * @ApiRoute(
 *     "/api/download-export/",
 *     section="Export",
 * )
 */
class DownloadExportPresenter extends Presenter
{
	/** @var string */
	public $exportsDirectory;

	/** @var ILogger @inject */
	public $auditing;

	/**
	 * Provides current version of exported data.
	 * This resource returns either HTTP code 200 and binary data, or 404 and no data.
	 *
	 * Successes & errors:
	 *  - Returns HTTP 200 with binary content
	 *  - Returns HTTP 404 when export invalid or file is missing
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
		$metadata = $this->getLatestMetadata();
		if (!$metadata) {
			Debugger::log('Could not determine latest export, invalid latest.json or invalid metadata.', ILoggerTracy::EXCEPTION);
			throw new BadRequestException('Could not determine latest export.');
		}
		// Auditing
		$dumpIdentification = $metadata['name'];
		$dumpTimestamp = $metadata['exported'];
		$this->auditing->logAccess(AuditedSubject::DATA_EXPORT, "Access data export [{$dumpIdentification}] from [{$dumpTimestamp}].", AuditedReason::REQUESTED_EXPORT);
		// Pass on to Apache to handle the file sending
		$this->sendResponse(new XSendFileResponse($this->exportsDirectory . '/' . $metadata['name']));
		$this->terminate();
	}

	/**
	 * Returns array with keys name (filename of latest dump) and exported (string date when latest dump was created) or null when not available.
	 *
	 * @return array|null
	 */
	private function getLatestMetadata(): ?array
	{
		$latestPath = $this->exportsDirectory .  '/latest.json';
		if (file_exists($latestPath) && is_file($latestPath) && is_readable($latestPath)) {
			try {
				$content = Json::decode(file_get_contents($latestPath), Json::FORCE_ARRAY);
			} catch (JsonException $ex) {
				return null;
			}
			$dataFilePath = $this->exportsDirectory . '/' . $content['data'];
			$metaFilePath = $this->exportsDirectory . '/' . $content['meta'];
			if (file_exists($dataFilePath) && is_file($dataFilePath) && is_readable($dataFilePath) &&
				file_exists($metaFilePath) && is_file($metaFilePath) && is_readable($metaFilePath)) {
				try {
					$meta = Json::decode(file_get_contents($metaFilePath), Json::FORCE_ARRAY);
				} catch (JsonException $ex) {
					return null;
				}
				if (!isset($meta['exported'])) {
					return null;
				}
				$meta['name'] = $content['data'];
				return $meta;
			}
		}
		return null;
	}
}
