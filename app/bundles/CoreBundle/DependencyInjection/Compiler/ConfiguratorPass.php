<?php

namespace Milex\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ConfiguratorPass.
 */
class ConfiguratorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('milex.configurator')) {
            return;
        }

        $configuratorDef = $container->findDefinition('milex.configurator');

        foreach ($container->findTaggedServiceIds('milex.configurator.step') as $id => $tags) {
            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : 0;
            $configuratorDef->addMethodCall('addStep', [new Reference($id), $priority]);
        }
    }
}
