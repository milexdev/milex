<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Integration\Interfaces;

use Milex\PluginBundle\Entity\Integration;
use Milex\PluginBundle\Integration\UnifiedIntegrationInterface;

interface IntegrationInterface extends UnifiedIntegrationInterface
{
    /**
     * Return the integration's name.
     */
    public function getName(): string;

    public function getDisplayName(): string;

    public function hasIntegrationConfiguration(): bool;

    public function getIntegrationConfiguration(): Integration;

    /**
     * @return mixed
     */
    public function setIntegrationConfiguration(Integration $integration);
}
