<?php

namespace Milex\CoreBundle\DependencyInjection\Compiler;

use Milex\CoreBundle\Templating\Engine\PhpEngine;
use Milex\CoreBundle\Templating\Helper\AssetsHelper;
use Milex\CoreBundle\Templating\Helper\FormHelper;
use Milex\CoreBundle\Templating\Helper\SlotsHelper;
use Milex\CoreBundle\Templating\Helper\TranslatorHelper;
use Milex\CoreBundle\Templating\TemplateNameParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TemplatingPass.
 */
class TemplatingPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('templating')) {
            return;
        }

        if ($container->hasDefinition('templating.helper.assets')) {
            $container->getDefinition('templating.helper.assets')
                ->setClass(AssetsHelper::class)
                ->addMethodCall('setPathsHelper', [new Reference('milex.helper.paths')])
                ->addMethodCall('setAssetHelper', [new Reference('milex.helper.assetgeneration')])
                ->addMethodCall('setBuilderIntegrationsHelper', [new Reference('milex.integrations.helper.builder_integrations')])
                ->addMethodCall('setInstallService', [new Reference('milex.install.service')])
                ->addMethodCall('setSiteUrl', ['%milex.site_url%'])
                ->addMethodCall('setVersion', ['%milex.secret_key%', MILEX_VERSION])
                ->setPublic(true);
        }

        if ($container->hasDefinition('templating.engine.php')) {
            $container->getDefinition('templating.engine.php')
                ->setClass(PhpEngine::class)
                ->addMethodCall(
                    'setDispatcher',
                    [new Reference('event_dispatcher')]
                )
                ->addMethodCall(
                    'setRequestStack',
                    [new Reference('request_stack')]
                )
                ->setPublic(true);
        }

        if ($container->hasDefinition('debug.templating.engine.php')) {
            $container->getDefinition('debug.templating.engine.php')
                ->setClass(PhpEngine::class)
                ->addMethodCall(
                    'setDispatcher',
                    [new Reference('event_dispatcher')]
                )
                ->addMethodCall(
                    'setRequestStack',
                    [new Reference('request_stack')]
                )
                ->setPublic(true);
        }

        if ($container->hasDefinition('templating.helper.slots')) {
            $container->getDefinition('templating.helper.slots')
                ->setClass(SlotsHelper::class)
                ->setPublic(true);
        }

        if ($container->hasDefinition('templating.name_parser')) {
            $container->getDefinition('templating.name_parser')
                ->setClass(TemplateNameParser::class)
                ->setPublic(true);
        }

        if ($container->hasDefinition('templating.helper.form')) {
            $container->getDefinition('templating.helper.form')
                ->setClass(FormHelper::class)
                ->setPublic(true);
        }

        if ($container->hasDefinition('templating.helper.translator')) {
            $container->getDefinition('templating.helper.translator')
                ->setClass(TranslatorHelper::class)
                ->setPublic(true);
        }
    }
}
