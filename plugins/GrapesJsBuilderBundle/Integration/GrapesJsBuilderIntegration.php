<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\Integration;

use Milex\IntegrationsBundle\Integration\BasicIntegration;
use Milex\IntegrationsBundle\Integration\ConfigurationTrait;
use Milex\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class GrapesJsBuilderIntegration extends BasicIntegration implements BasicInterface
{
    use ConfigurationTrait;

    public const NAME         = 'grapesjsbuilder';
    public const DISPLAY_NAME = 'GrapesJS';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/GrapesJsBuilderBundle/Assets/img/grapesjsbuilder.png';
    }
}
