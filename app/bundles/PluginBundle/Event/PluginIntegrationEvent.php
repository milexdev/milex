<?php

namespace Milex\PluginBundle\Event;

use Milex\PluginBundle\Entity\Integration;
use Milex\PluginBundle\Integration\UnifiedIntegrationInterface;

/**
 * Class PluginIntegrationEvent.
 */
class PluginIntegrationEvent extends AbstractPluginIntegrationEvent
{
    public function __construct(UnifiedIntegrationInterface $integration)
    {
        $this->integration = $integration;
    }

    /**
     * @return Integration
     */
    public function getEntity()
    {
        return $this->integration->getIntegrationSettings();
    }

    public function setEntity(Integration $integration)
    {
        $this->integration->setIntegrationSettings($integration);
    }
}
