<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\Integration;

use Milex\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Milex\IntegrationsBundle\Helper\IntegrationsHelper;
use Milex\PluginBundle\Entity\Integration;

class Config
{
    /**
     * @var IntegrationsHelper
     */
    private $integrationsHelper;

    public function __construct(IntegrationsHelper $integrationsHelper)
    {
        $this->integrationsHelper = $integrationsHelper;
    }

    public function isPublished(): bool
    {
        try {
            $integration = $this->getIntegrationEntity();

            return (bool) $integration->getIsPublished() ?: false;
        } catch (IntegrationNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return mixed[]
     */
    public function getFeatureSettings(): array
    {
        try {
            $integration = $this->getIntegrationEntity();

            return $integration->getFeatureSettings() ?: [];
        } catch (IntegrationNotFoundException $e) {
            return [];
        }
    }

    /**
     * @throws IntegrationNotFoundException
     */
    public function getIntegrationEntity(): Integration
    {
        $integrationObject = $this->integrationsHelper->getIntegration(GrapesJsBuilderIntegration::NAME);

        return $integrationObject->getIntegrationConfiguration();
    }
}
