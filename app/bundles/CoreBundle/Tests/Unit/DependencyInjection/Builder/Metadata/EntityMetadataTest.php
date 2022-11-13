<?php

namespace Milex\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use Milex\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use Milex\CoreBundle\DependencyInjection\Builder\Metadata\EntityMetadata;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    /**
     * @var BundleMetadata
     */
    private $metadata;

    protected function setUp(): void
    {
        $metadataArray = [
            'isPlugin'          => true,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MilexCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'Milex\\CoreBundle',
            'symfonyBundleName' => 'MilexCoreBundle',
            'bundleClass'       => '\\Milex\\CoreBundle',
        ];

        $this->metadata = new BundleMetadata($metadataArray);
    }

    public function testOrmAndSerializerConfigsFound()
    {
        $entityMetadata = new EntityMetadata($this->metadata);
        $entityMetadata->build();

        $this->assertEquals(
            [
                'dir'       => 'Entity',
                'type'      => 'staticphp',
                'prefix'    => 'Milex\\CoreBundle\\Entity',
                'mapping'   => true,
                'is_bundle' => true,
            ],
            $entityMetadata->getOrmConfig()
        );

        $this->assertEquals(
            [
                'namespace_prefix' => 'Milex\\CoreBundle\\Entity',
                'path'             => '@MilexCoreBundle/Entity',
            ],
            $entityMetadata->getSerializerConfig()
        );
    }
}
