<?php

namespace Milex\UserBundle;

use Milex\UserBundle\DependencyInjection\Firewall\Factory\PluginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MilexUserBundle.
 */
class MilexUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new PluginFactory());
    }
}
