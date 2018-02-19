<?php declare(strict_types=1);

use App\Utils\Normalize;
use League\Csv\Reader;
use League\Csv\Writer;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../app/Utils/Normalize.php';

/*
 * Ad-hoc script for local processing of data from supreme court
 * For execution provide:
 *  - Black list file (not to be imported as it already has the official data)
 *  - Civil processes
 *  - Penalty processes
 *
 * Import after the csv with official data with dru run first (to verify no overwritting has happened), or import first (but also with dry-run) to prevent overwriting of older data.
 */

// Load blacklist
$csv = Reader::createFromPath('/Users/jan/Desktop/2018_02_19.csv', 'r');
$blacklist = array_flip(array_map(function ($input) { return Normalize::registryMark($input); } , array_column($csv->setOffset(1)->fetchAll(), 0)));


$output = [];
// Process civil
$csv = Reader::createFromPath('/Users/jan/Desktop/18_02_15_poskytnutí informace_výsledky řízení_civilni.csv', 'r');
$res = $csv->setOffset(1)->fetchAssoc(['number', 'date_creation', 'registry_mark_1','registry_mark_2','registry_mark_3','registry_mark_4', 'date_decision', 'result']);
foreach ($res as $row) {
	$row['registry_mark'] = Normalize::registryMark(sprintf('%s %s %s/%s', $row['registry_mark_1'], $row['registry_mark_2'], $row['registry_mark_3'], $row['registry_mark_4']));
	if (!isset($blacklist[$row['registry_mark']])) {
		$output[] = [
			$row['registry_mark'],
			$row['date_creation'],
			$row['date_decision'],
			$row['result'],
		];
	}
}

// Process penalty
$csv = Reader::createFromPath('/Users/jan/Desktop/18_02_15_poskytnutí informace_výsledky řízení_trestni.csv', 'r');
$res = $csv->setOffset(1)->fetchAssoc(['registry_mark_1','registry_mark_2','registry_mark_3','registry_mark_4', 'date_creation', 'subject', 'date_decision', 'result']);
foreach ($res as $row) {
	$row['registry_mark'] = Normalize::registryMark(sprintf('%s %s %s/%s', $row['registry_mark_1'], $row['registry_mark_2'], $row['registry_mark_3'], $row['registry_mark_4']));
	unset($row['number'], $row['registry_mark_1'], $row['registry_mark_2'], $row['registry_mark_3'], $row['registry_mark_4']);
	if (!isset($blacklist[$row['registry_mark']])) {
		$output[] = [
			$row['registry_mark'],
			$row['date_creation'],
			$row['date_decision'],
			$row['result'],
		];
	}
}

$csv = Writer::createFromFileObject(new SplTempFileObject());

//we insert the CSV header
$csv->insertOne(['registry_mark', 'date_creation', 'date_decision', 'result']);
$csv->insertAll($output);
ob_start();
$csv->output();
$a = ob_get_clean();
file_put_contents(__DIR__ . '/2018-02-15-not-populated-without-advocates.csv', $a);
echo count($output);