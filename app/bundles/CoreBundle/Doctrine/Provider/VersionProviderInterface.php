<?php

namespace Milex\CoreBundle\Doctrine\Provider;

interface VersionProviderInterface
{
    public function getVersion(): string;

    public function isMariaDb(): bool;

    public function isMySql(): bool;
}
