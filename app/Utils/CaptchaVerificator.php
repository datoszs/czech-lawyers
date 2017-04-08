<?php


namespace App\Utils;


use App\Exceptions\GoogleCaptchaInvalidConfiguration;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\Json;

class CaptchaVerificator
{
	use SmartObject;

	private const URL = 'https://www.google.com/recaptcha/api/siteverify';

	/** @var string */
	private $secret;

	/** @var Request */
	private $request;

	public function __construct($config, Request $request)
	{
		if (!isset($config['secret'])) {
			throw new GoogleCaptchaInvalidConfiguration('Missing [google.captcha.secret] configuration value.');
		}
		$this->secret = $config['secret'];
		$this->request = $request;
	}

	public function verify($response)
	{
		// See https://developers.google.com/recaptcha/docs/verify
		$data = [
			'secret' => $this->secret,
			'response' => $response,
			'remoteip' => $this->request->getRemoteAddress()
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,static::URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		curl_close ($ch);

		if ($response) {
			$responseJson = Json::decode($response);
			if ($responseJson && isset($responseJson->success) && $responseJson->success) {
				return true;
			}
		}
		return false;
	}

}