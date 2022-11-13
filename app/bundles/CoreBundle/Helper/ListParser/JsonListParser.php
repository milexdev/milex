<?php

namespace Milex\CoreBundle\Helper\ListParser;

use Milex\CoreBundle\Helper\ListParser\Exception\FormatNotSupportedException;

class JsonListParser implements ListParserInterface
{
    public function parse($list): array
    {
        if (!is_string($list)) {
            throw new FormatNotSupportedException();
        }

        $parsedList = json_decode($list, true);
        if (!is_array($parsedList)) {
            throw new FormatNotSupportedException();
        }

        return $parsedList;
    }
}
