<?php declare(strict_types=1);

namespace App\Utils;

use App\Exceptions\MissingApacheXSendFileModuleException;
use Nette\Application\BadRequestException;
use Nette\Application\IResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse as HttpIResponse;
use Nette\SmartObject;


/**
 * File download response which handles file sending to Apache instead of PHP
 */
class XSendFileResponse implements IResponse
{
	use SmartObject;

	/** @var bool */
	public $resuming = true;

	/** @var string */
	private $file;

	/** @var string */
	private $contentType;

	/** @var string */
	private $name;

	/** @var bool */
	private $forceDownload;


	/**
	 * @param string 		$file Absolute file path to existing and accessible file withing DocumentRoot or XSendFilePath
	 * @param string|null	$name User name of file
	 * @param string 		$contentType imposed file name
	 * @param bool|string 	$forceDownload MIME content type
	 *
	 * @throws BadRequestException when no such file exists
	 * @throws MissingApacheXSendFileModuleException when xsendfile Apache module is missing
	 */
	public function __construct($file, $name = null, $contentType = null, $forceDownload = true)
	{
		if (function_exists('apache_get_modules') && !in_array('mod_xsendfile', apache_get_modules())) {
			throw new MissingApacheXSendFileModuleException('The Apache XSendFile module is not loaded.');
		}
		if (!is_file($file)) {
			throw new BadRequestException("File '$file' doesn't exist.");
		}

		$this->file = $file;
		$this->name = $name ? $name : basename($file);
		$this->contentType = $contentType ? $contentType : 'application/octet-stream';
		$this->forceDownload = $forceDownload;
	}


	/**
	 * Returns the path to a downloaded file.
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}


	/**
	 * Returns the file name.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(IRequest $httpRequest, HttpIResponse $httpResponse)
	{
		$httpResponse->setContentType('application/octet-stream');
		$httpResponse->setHeader('Content-Disposition',
			($this->forceDownload ? 'attachment' : 'inline')
				. '; filename="' . $this->name . '"'
				. '; filename*=utf-8\'\'' . rawurlencode($this->name));

		$httpResponse->setHeader('X-Sendfile', $this->file);
	}
}
