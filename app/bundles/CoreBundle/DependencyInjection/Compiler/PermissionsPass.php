<?php

declare(strict_types=1);

namespace Milex\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PermissionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $corePermissions = $container->findDefinition('milex.security');

        foreach ($container->findTaggedServiceIds('milex.permissions') as $id => $tags) {
            $permissionObject = $container->findDefinition($id);
            $corePermissions->addMethodCall('setPermissionObject', [$permissionObject]);
        }
    }
}
