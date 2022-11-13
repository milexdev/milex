<?php

namespace Milex\SmsBundle;

use Milex\PluginBundle\Bundle\PluginBundleBase;
use Milex\SmsBundle\DependencyInjection\Compiler\SmsTransportPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MilexSmsBundle.
 */
class MilexSmsBundle extends PluginBundleBase
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SmsTransportPass());
    }
}
