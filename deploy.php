<?php
echo "Deployment script\n";
echo "--------------------------------\n";

if (!file_exists('./.git')) {
	die("Error: This deployment script should be executed only from the project root. Terminating now.\n");
}
if (!isset($argv[1]) || !in_array($argv[1], ['production', 'development'])) {
	die("Error: The deployment environment is empty or invalid.\n");
}
if (file_exists('.deployment-in-progress')) {
	die("Error: There was already deployment outgoing, fix issues and remove the ./.deployment-in-progress file.\n");
}

$environment = $argv[1];

touch('.deployment-in-progress');

// Fetch and pull new sources
$state = 0;
system('git pull', $state);
if ($state !== 0) {
	die("Error: Git pull failed\n");
}
// Update PHP dependencies
$state = 0;
system('php composer.phar install', $state);
if ($state !== 0) {
	die("Error: Update of PHP dependencies via Composer failed.\n");
}

// Update Node.js dependencies
$state = 0;
system('npm install', $state);
if ($state !== 0) {
	die("Error: Update of Node.js dependencies via node failed.\n");
}

// Build frontend
$state = 0;
system('npm run build', $state);
if ($state !== 0) {
	die("Error: Building frontend has failed.\n");
}

// Remove Nette cache
$state = 0;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // Windows systems
	system('rmdir /s/q temp\cache', $state);
	system('mkdir temp\cache');
} else {
	system('rm -rf temp/cache/*', $state); // Linux systems
}
if ($state !== 0) {
	die("Error: Nette cache could not been deleted, please do it manually.\n");
}

// Regenerate files via Gulp
$state = 0;
if ($environment === 'production') {
	system('gulp production', $state);
} else {
	system('gulp default', $state);
}
if ($state !== 0) {
	die("Error: Update of production files could not been finished.\n");
}

// Run database migrations
$state = 0;
if ($environment === 'production') {
	system('php www/index.php migrations:continue --production', $state);
} else {
	system('php www/index.php migrations:continue', $state);
}
if ($state !== 0) {
	die("Error: Database migrations failed.\n");
}

if (file_exists('.deployment-in-progress')) {
	unlink('.deployment-in-progress');
}

if ($environment === 'development') {
	echo "Info: If you want to automatically rebuild assets please run $ gulp development\n";
}

echo "Info: Finished successfully\n";
