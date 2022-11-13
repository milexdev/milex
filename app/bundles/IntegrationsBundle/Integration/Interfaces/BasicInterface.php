<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Integration\Interfaces;

use Milex\PluginBundle\Integration\UnifiedIntegrationInterface;

interface BasicInterface extends UnifiedIntegrationInterface
{
    /**
     * Return the integration's name.
     */
    public function getName(): string;

    public function getIcon(): string;
}
