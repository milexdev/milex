<?php

namespace Milex\CoreBundle\Helper\ListParser;

use Milex\CoreBundle\Helper\ListParser\Exception\FormatNotSupportedException;

interface ListParserInterface
{
    /**
     * @param mixed $list
     *
     * @throws FormatNotSupportedException
     */
    public function parse($list): array;
}
