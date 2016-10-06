<?php
echo "Deployment script\n";
echo "--------------------------------\n";

if (!file_exists('./.git')) {
	die("Error: This deployment script should be executed only from the project root. Terminating now.\n");
}
if (file_exists('.deployment-in-progress')) {
	die("Error: There was already deployment outgoing, fix issues and remove the ./.deployment-in-progress file.\n");
}

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
system('npm update', $state);
if ($state !== 0) {
	die("Error: Update of Node.js dependencies via node failed.\n");
}

// Remove Nette cache
$state = 0;
system('rm -rf temp/cache/*', $state);
if ($state !== 0) {
	die("Error: Nette cache could not been deleted, please do it manually.\n");
}

// Regenerate files via Gulp
$state = 0;
system('gulp production', $state);
if ($state !== 0) {
	die("Error: Update of production files could not been finished.\n");
}

// Run database migrations
$state = 0;
system('php www/index.php migrations:continue --production');
if ($state !== 0) {
	die("Error: Database migrations failed.\n");
}

if (file_exists('.deployment-in-progress')) {
	unlink('.deployment-in-progress');
}

echo "Info: Finished successfully\n";
