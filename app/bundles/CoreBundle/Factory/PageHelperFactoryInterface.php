<?php

namespace Milex\CoreBundle\Factory;

use Milex\CoreBundle\Helper\PageHelperInterface;

interface PageHelperFactoryInterface
{
    public function make(string $sessionPrefix, int $page): PageHelperInterface;
}
