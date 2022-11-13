<?php

namespace Milex\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use Milex\AssetBundle\Security\Permissions\AssetPermissions;
use Milex\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use Milex\CoreBundle\DependencyInjection\Builder\Metadata\PermissionClassMetadata;
use Milex\CoreBundle\Security\Permissions\SystemPermissions;
use PHPUnit\Framework\TestCase;

class PermissionClassMetadataTest extends TestCase
{
    public function testPermissionsFound()
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MilexCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'Milex\\CoreBundle',
            'symfonyBundleName' => 'MilexCoreBundle',
            'bundleClass'       => '\\Milex\\CoreBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertTrue(isset($metadata->toArray()['permissionClasses'][SystemPermissions::class]));
        $this->assertCount(1, $metadata->toArray()['permissionClasses']);
    }

    public function testCompatibilityWithPermissionServices()
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Asset',
            'bundle'            => 'AssetBundle',
            'relative'          => 'app/bundles/MilexAssetBundle',
            'directory'         => __DIR__.'/../../../../../../AssetBundle',
            'namespace'         => 'Milex\\AssetBundle',
            'symfonyBundleName' => 'MilexAssetBundle',
            'bundleClass'       => '\\Milex\\AssetBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertTrue(isset($metadata->toArray()['permissionClasses'][AssetPermissions::class]));
    }
}
