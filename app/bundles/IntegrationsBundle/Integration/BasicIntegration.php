<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Integration;

use Milex\IntegrationsBundle\Integration\BC\BcIntegrationSettingsTrait;
use Milex\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;

abstract class BasicIntegration implements IntegrationInterface
{
    use BcIntegrationSettingsTrait;
    use ConfigurationTrait;

    public function getDisplayName(): string
    {
        return $this->getName();
    }
}
