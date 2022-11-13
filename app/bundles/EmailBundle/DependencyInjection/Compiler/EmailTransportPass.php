<?php

namespace Milex\EmailBundle\DependencyInjection\Compiler;

use Milex\EmailBundle\Model\TransportType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EmailTransportPass.
 */
class EmailTransportPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('milex.email.transport_type')) {
            return;
        }

        $definition     = $container->getDefinition('milex.email.transport_type');
        $taggedServices = $container->findTaggedServiceIds('milex.email_transport');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addTransport', [
                $id,
                !empty($tags[0][TransportType::TRANSPORT_ALIAS]) ? $tags[0][TransportType::TRANSPORT_ALIAS] : $id,
                !empty($tags[0][TransportType::FIELD_HOST]),
                !empty($tags[0][TransportType::FIELD_PORT]),
                !empty($tags[0][TransportType::FIELD_USER]),
                !empty($tags[0][TransportType::FIELD_PASSWORD]),
                !empty($tags[0][TransportType::FIELD_API_KEY]),
            ]);
        }
    }
}
