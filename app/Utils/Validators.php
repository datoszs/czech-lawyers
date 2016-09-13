<?php
namespace App\Utils;


use Nette\Utils\Finder;

trait Validators
{

	public function validateCrawlerDirectory($directory)
	{
		$working = $directory . '/working/';
		$result = $directory . '/result/';
		return is_dir($working)
			&& is_dir($result)
			&& Finder::find()->exclude('.*')->in($working)->count() == 0
			&& Finder::find()->exclude('.*')->in($result)->count() == 0;
	}

	public function validateEmptyDirectory($directory)
	{
		$count = Finder::find()->exclude('.*')->in($directory)->count();
		return $count == 0;
	}

	public function validateInputDirectory($directory)
	{
		$metadataFile = $directory . '/metadata.csv';
		$documentsDir = $directory . '/documents';
		$count = Finder::find()->exclude('.*')->in($directory)->count();

		return is_dir($documentsDir)
			&& is_readable($documentsDir)
			&& is_file($metadataFile)
			&& is_readable($documentsDir)
			&& $count === 2;
	}
}