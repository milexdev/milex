<?php

namespace Milex\CoreBundle\Helper;

use Milex\CoreBundle\Exception\FileInvalidException;

class FileProperties
{
    /**
     * @param string $filename
     *
     * @return int
     *
     * @throws FileInvalidException
     */
    public function getFileSize($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new FileInvalidException();
        }

        return filesize($filename);
    }
}
