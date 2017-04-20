<?php
/**
 * Date: 20.04.2017
 * Time: 10:41
 */

namespace ait\utilities\helpers;


class MIMEHelper
{
    public function getFileType($filename)
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime_type = finfo_file($fileinfo, $filename);
        finfo_close($fileinfo);
        return $file_mime_type;
    }
}