<?php

namespace Milex\EmailBundle;

use Milex\EmailBundle\DependencyInjection\Compiler\EmailTransportPass;
use Milex\EmailBundle\DependencyInjection\Compiler\SpoolTransportPass;
use Milex\EmailBundle\DependencyInjection\Compiler\StatHelperPass;
use Milex\EmailBundle\DependencyInjection\Compiler\SwiftmailerDynamicMailerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MilexEmailBundle.
 */
class MilexEmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SpoolTransportPass());
        $container->addCompilerPass(new EmailTransportPass());
        $container->addCompilerPass(new SwiftmailerDynamicMailerPass());
        $container->addCompilerPass(new StatHelperPass());
    }
}
