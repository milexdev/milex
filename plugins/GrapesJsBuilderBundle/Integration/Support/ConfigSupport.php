<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\Integration\Support;

use Milex\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Milex\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MilexPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration;

class ConfigSupport extends GrapesJsBuilderIntegration implements ConfigFormInterface
{
    use DefaultConfigFormTrait;
}
