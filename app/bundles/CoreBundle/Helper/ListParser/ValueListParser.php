<?php

namespace Milex\CoreBundle\Helper\ListParser;

use Milex\CoreBundle\Helper\ListParser\Exception\FormatNotSupportedException;

class ValueListParser implements ListParserInterface
{
    public function parse($list): array
    {
        if (is_array($list)) {
            throw new FormatNotSupportedException();
        }

        return [$list => $list];
    }
}
