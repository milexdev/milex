<?php

namespace Milex\CoreBundle\Tests\Unit\DependencyInjection\Builder;

use Milex\CoreBundle\DependencyInjection\Builder\BundleMetadataBuilder;
use Milex\CoreBundle\Security\Permissions\SystemPermissions;
use MilexPlugin\MilexFocusBundle\Security\Permissions\FocusPermissions;
use PHPUnit\Framework\TestCase;

class BundleMetadataBuilderTest extends TestCase
{
    /**
     * @var array
     */
    private $paths;

    protected function setUp(): void
    {
        // Used in paths_helper
        $root = __DIR__.'/../../../../../../../app';

        /** @var array $paths */
        include __DIR__.'/../../../../../../config/paths_helper.php';

        if (!isset($paths)) {
            throw new \Exception('$paths is not set');
        }

        $this->paths = $paths;
    }

    public function testCoreBundleMetadataLoaded()
    {
        $bundles = ['MilexCoreBundle' => 'Milex\CoreBundle\MilexCoreBundle'];

        $builder  = new BundleMetadataBuilder($bundles, $this->paths);
        $metadata = $builder->getCoreBundleMetadata();

        $this->assertEquals([], $builder->getPluginMetadata());
        $this->assertTrue(isset($metadata['MilexCoreBundle']));

        $bundleMetadata = $metadata['MilexCoreBundle'];

        $this->assertFalse($bundleMetadata['isPlugin']);
        $this->assertEquals('Core', $bundleMetadata['base']);
        $this->assertEquals('CoreBundle', $bundleMetadata['bundle']);
        $this->assertEquals('MilexCoreBundle', $bundleMetadata['symfonyBundleName']);
        $this->assertEquals('app/bundles/CoreBundle', $bundleMetadata['relative']);
        $this->assertEquals(realpath($this->paths['root']).'/app/bundles/CoreBundle', $bundleMetadata['directory']);
        $this->assertEquals('Milex\CoreBundle', $bundleMetadata['namespace']);
        $this->assertEquals('Milex\CoreBundle\MilexCoreBundle', $bundleMetadata['bundleClass']);
        $this->assertTrue(isset($bundleMetadata['permissionClasses']));
        $this->assertTrue(isset($bundleMetadata['permissionClasses'][SystemPermissions::class]));
        $this->assertTrue(isset($bundleMetadata['config']));
        $this->assertTrue(isset($bundleMetadata['config']['routes']));
    }

    public function testPluginMetadataLoaded()
    {
        $bundles = ['MilexFocusBundle' => 'MilexPlugin\MilexFocusBundle\MilexFocusBundle'];

        $builder  = new BundleMetadataBuilder($bundles, $this->paths);
        $metadata = $builder->getPluginMetadata();

        $this->assertEquals([], $builder->getCoreBundleMetadata());
        $this->assertTrue(isset($metadata['MilexFocusBundle']));
        $bundleMetadata = $metadata['MilexFocusBundle'];

        $this->assertTrue($bundleMetadata['isPlugin']);
        $this->assertEquals('MilexFocus', $bundleMetadata['base']);
        $this->assertEquals('MilexFocusBundle', $bundleMetadata['bundle']);
        $this->assertEquals('MilexFocusBundle', $bundleMetadata['symfonyBundleName']);
        $this->assertEquals('plugins/MilexFocusBundle', $bundleMetadata['relative']);
        $this->assertEquals(realpath($this->paths['root']).'/plugins/MilexFocusBundle', $bundleMetadata['directory']);
        $this->assertEquals('MilexPlugin\MilexFocusBundle', $bundleMetadata['namespace']);
        $this->assertEquals('MilexPlugin\MilexFocusBundle\MilexFocusBundle', $bundleMetadata['bundleClass']);
        $this->assertTrue(isset($bundleMetadata['permissionClasses']));
        $this->assertTrue(isset($bundleMetadata['permissionClasses'][FocusPermissions::class]));
        $this->assertTrue(isset($bundleMetadata['config']));
        $this->assertTrue(isset($bundleMetadata['config']['routes']));
    }

    public function testSymfonyBundleIgnored()
    {
        $bundles = ['FooBarBundle' => 'Foo\Bar\BarBundle'];

        $builder = new BundleMetadataBuilder($bundles, $this->paths);
        $this->assertEquals([], $builder->getCoreBundleMetadata());
        $this->assertEquals([], $builder->getPluginMetadata());
    }
}
