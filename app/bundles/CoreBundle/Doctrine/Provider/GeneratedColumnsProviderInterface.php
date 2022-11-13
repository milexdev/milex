<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Doctrine\Provider;

use Milex\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumns;

interface GeneratedColumnsProviderInterface
{
    public function getGeneratedColumns(): GeneratedColumns;

    public function generatedColumnsAreSupported(): bool;

    public function getMinimalSupportedVersion(): string;
}
