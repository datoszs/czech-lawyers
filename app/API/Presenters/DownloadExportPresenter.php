<?php declare(strict_types=1);

namespace App\APIModule\Presenters;


use App\Auditing\AuditedReason;
use App\Auditing\AuditedSubject;
use App\Auditing\ILogger;
use App\Utils\XSendFileResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Random;
use Tracy\Debugger;
use Tracy\ILogger as ILoggerTracy;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for obtaining download of current exported data.
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

	/** @var Session @inject */
	public $session;

	/**
	 * Provides access to current version of exported data.
	 *
	 * Behaviour is determined by presence of token:
	 *  - Missing token: generate one-time, session dependent, link for download (containing token).
	 *  - Present token: attempt to download if temporary token exists and is valid.
	 *
	 * Token validity is 1 minutes and can be used only once.
	 *
	 * When token is missing, following output is returned:
	 * <json>
	 *     {
	 *         "link": "https://www.example.com/api/download-export/sY345i"
	 *     }
	 * </json>
	 *
	 * When token is present, either binary content xor 404 is returned.
	 *
	 * Successes & errors:
	 *  - Returns HTTP 200 with binary content xor with link key when token was prepared.
	 *  - Returns HTTP 404 when export invalid or file is missing
	 *
	 * @ApiRoute(
	 *     "/api/download-export[/<token>]",
	 *     parameters={
	 *         "token"={
	 *             "requirement": ".*",
	 *             "type": "string",
	 *             "description": "Download request token.",
	 *             "default": null
	 *         },
	 *     },
	 *     section="Export",
	 *     presenter="API:DownloadExport",
	 *     tags={
	 *         "public",
	 *     },
	 * )
	 * @throws AbortException when redirection happens
	 */
	public function actionRead(?string $token = null) : void
	{
		$section = $this->session->getSection(static::class);
		$section->setExpiration('600');
		if (!$token) {
			// Prepare download token
			$token = Random::generate(10);
			$section->offsetSet($token, true);
			// Send the link out
			$this->sendJson([
				'link' => $this->prepareLink($token)
			]);
			$this->terminate();
		} elseif ($section->offsetExists($token)) {
			$metadata = $this->getLatestMetadata();
			if (!$metadata) {
				Debugger::log('Could not determine latest export, invalid latest.json or invalid metadata.', ILoggerTracy::EXCEPTION);
				throw new BadRequestException('Could not determine latest export.');
			}
			// Auditing
			$dumpIdentification = $metadata['name'];
			$dumpTimestamp = $metadata['exported'];
			$this->auditing->logAccess(AuditedSubject::DATA_EXPORT, "Access data export [{$dumpIdentification}] from [{$dumpTimestamp}].", AuditedReason::REQUESTED_EXPORT);
			// Invalidate token
			$section->offsetUnset($token);
			// Pass on to Apache to handle the file sending
			$this->sendResponse(new XSendFileResponse($this->exportsDirectory . '/' . $metadata['name']));
			$this->terminate();
		} else {
			throw new BadRequestException();
		}
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

	private function prepareLink(string $token): string
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'].'/';

		return $protocol . $domainName . '/api/download-export/' . $token;
	}
}
