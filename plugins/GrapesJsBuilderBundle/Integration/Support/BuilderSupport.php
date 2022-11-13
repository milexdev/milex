<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\Integration\Support;

use Milex\IntegrationsBundle\Integration\Interfaces\BuilderInterface;
use MilexPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration;

class BuilderSupport extends GrapesJsBuilderIntegration implements BuilderInterface
{
    private $featuresSupported = ['email', 'page'];

    public function isSupported(string $featureName): bool
    {
        return in_array($featureName, $this->featuresSupported);
    }
}
