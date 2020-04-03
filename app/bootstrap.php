<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

if (PHP_SAPI !== 'cli' && (file_exists(__DIR__ . '/../.deployment-in-progress') || file_exists(__DIR__ . '/../.deployment'))) {
	// AJAX requests needs special treatment
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		header('HTTP/1.1 503 Service Unavailable');
		header('Retry-After: 900'); // 15 minutes in seconds
		echo json_encode(['deployment' => TRUE]);
		exit;
	} else {
		include(__DIR__ . '/../www/.maintenance.phtml');
		exit;
	}
}

$allowedDebugAddresses = ['127.0.0.1', '::1', '192.168.99.1', '10.211.55.2'];
if (((bool) getenv('TRACY_DEBUG_ENABLE')) === true) {
	$allowedDebugAddresses[] = $_SERVER['REMOTE_ADDR'];
}
$configurator->setDebugMode($allowedDebugAddresses);
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/Config/config.neon');
$configurator->addConfig(__DIR__ . '/Config/config.local.neon');

$container = $configurator->createContainer();

return $container;
