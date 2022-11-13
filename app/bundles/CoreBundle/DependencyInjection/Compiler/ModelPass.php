<?php

namespace Milex\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ModelPass.
 */
class ModelPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('milex.model') as $id => $tags) {
            $definition = $container->findDefinition($id);

            $modelClass = $definition->getClass();
            $reflected  = new \ReflectionClass($modelClass);

            if ($reflected->hasMethod('setEntityManager')) {
                $definition->addMethodCall('setEntityManager', [new Reference('doctrine.orm.entity_manager')]);
            }

            if ($reflected->hasMethod('setSecurity')) {
                $definition->addMethodCall('setSecurity', [new Reference('milex.security')]);
            }

            if ($reflected->hasMethod('setDispatcher')) {
                $definition->addMethodCall('setDispatcher', [new Reference('event_dispatcher')]);
            }

            if ($reflected->hasMethod('setTranslator')) {
                $definition->addMethodCall('setTranslator', [new Reference('translator')]);
            }

            if ($reflected->hasMethod('setUserHelper')) {
                $definition->addMethodCall('setUserHelper', [new Reference('milex.helper.user')]);
            }

            if ($reflected->hasMethod('setCoreParametersHelper')) {
                $definition->addMethodCall('setCoreParametersHelper', [new Reference('milex.helper.core_parameters')]);
            }

            if ($reflected->hasMethod('setRouter')) {
                $definition->addMethodCall('setRouter', [new Reference('router')]);
            }

            if ($reflected->hasMethod('setLogger')) {
                $definition->addMethodCall('setLogger', [new Reference('monolog.logger.milex')]);
            }

            if ($reflected->hasMethod('setSession')) {
                $definition->addMethodCall('setSession', [new Reference('session')]);
            }
        }
    }
}
