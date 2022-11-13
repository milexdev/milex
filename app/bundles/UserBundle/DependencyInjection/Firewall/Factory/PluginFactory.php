<?php

namespace Milex\UserBundle\DependencyInjection\Firewall\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PluginFactory implements SecurityFactoryInterface
{
    /**
     * @param $id
     * @param $config
     * @param $userProvider
     * @param $defaultEntryPoint
     *
     * @return array
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.milex.'.$id;
        $container->setDefinition($providerId, new ChildDefinition('milex.user.preauth_authenticator'))
            ->replaceArgument(3, new Reference($userProvider))
            ->replaceArgument(4, $id);

        $listenerId = 'security.authentication.listener.milex.'.$id;
        $container->setDefinition($listenerId, new ChildDefinition('milex.security.authentication_listener'))
            ->replaceArgument(5, $id);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'milex_plugin_auth';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
