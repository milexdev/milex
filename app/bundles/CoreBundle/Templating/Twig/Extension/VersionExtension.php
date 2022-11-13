<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Templating\Twig\Extension;

use Milex\CoreBundle\Helper\AppVersion;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VersionExtension extends AbstractExtension
{
    private AppVersion $appVersion;

    public function __construct(AppVersion $appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('milexAppVersion', [$this, 'getVersion']),
        ];
    }

    public function getVersion(): string
    {
        return $this->appVersion->getVersion();
    }
}
