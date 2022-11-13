<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle;

use Milex\IntegrationsBundle\Bundle\AbstractPluginBundle;
use Milex\IntegrationsBundle\DependencyInjection\Compiler\AuthenticationIntegrationPass;
use Milex\IntegrationsBundle\DependencyInjection\Compiler\BuilderIntegrationPass;
use Milex\IntegrationsBundle\DependencyInjection\Compiler\ConfigIntegrationPass;
use Milex\IntegrationsBundle\DependencyInjection\Compiler\IntegrationsPass;
use Milex\IntegrationsBundle\DependencyInjection\Compiler\SyncIntegrationsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IntegrationsBundle extends AbstractPluginBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new IntegrationsPass());
        $container->addCompilerPass(new AuthenticationIntegrationPass());
        $container->addCompilerPass(new SyncIntegrationsPass());
        $container->addCompilerPass(new ConfigIntegrationPass());
        $container->addCompilerPass(new BuilderIntegrationPass());
    }
}
