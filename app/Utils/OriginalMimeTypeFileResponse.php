<?php
namespace App\Utils\Responses;


use Nette\Application\Responses\FileResponse;
use Nette\Utils\Strings;

/**
 * File response with mime type pass through without forcing download.
 * When $forceDownload is null it is force only for non images.
 */
class OriginalMimeTypeFileResponse extends FileResponse
{

    public function __construct($file, $name = NULL, $contentType = NULL, $forceDownload = FALSE)
    {
        if (is_file($file) && !$contentType) {
            $contentType = mime_content_type($file);
        }
        if ($forceDownload === null) {
            $forceDownload = !Strings::startsWith($contentType, 'image/');
        }
        parent::__construct($file, $name, $contentType, (bool) $forceDownload);


    }
}
