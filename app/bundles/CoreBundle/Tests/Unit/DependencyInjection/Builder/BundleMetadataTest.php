<?php

namespace Milex\CoreBundle\Tests\Unit\DependencyInjection\Builder;

use Milex\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use PHPUnit\Framework\TestCase;

class BundleMetadataTest extends TestCase
{
    public function testGetters()
    {
        $metadataArray = [
            'isPlugin'          => true,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MilexCoreBundle',
            'directory'         => '/var/www/app/bundles/MilexCoreBundle',
            'namespace'         => 'Milex\\CoreBundle',
            'symfonyBundleName' => 'MilexCoreBundle',
            'bundleClass'       => '\\Milex\\CoreBundle',
        ];

        $metadata = new BundleMetadata($metadataArray);
        $this->assertSame($metadataArray['directory'], $metadata->getDirectory());
        $this->assertSame($metadataArray['namespace'], $metadata->getNamespace());
        $this->assertSame($metadataArray['bundle'], $metadata->getBaseName());
        $this->assertSame($metadataArray['symfonyBundleName'], $metadata->getBundleName());

        $metadata->setConfig(['foo' => 'bar']);
        $metadata->addPermissionClass('\Foo\Bar');

        $metadataArray['config']                        = ['foo' => 'bar'];
        $metadataArray['permissionClasses']['\Foo\Bar'] = '\Foo\Bar';
        $this->assertEquals($metadataArray, $metadata->toArray());
    }
}
