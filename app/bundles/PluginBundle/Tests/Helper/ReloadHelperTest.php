<?php

namespace Milex\PluginBundle\Tests\Helper;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Mapping\ClassMetadata;
use Milex\CoreBundle\Factory\MilexFactory;
use Milex\PluginBundle\Entity\Plugin;
use Milex\PluginBundle\Event\PluginInstallEvent;
use Milex\PluginBundle\Event\PluginUpdateEvent;
use Milex\PluginBundle\Helper\ReloadHelper;
use Milex\PluginBundle\PluginEvents;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReloadHelperTest extends \PHPUnit\Framework\TestCase
{
    private $factoryMock;

    /**
     * @var ReloadHelper
     */
    private $helper;

    /**
     * @var array
     */
    private $sampleAllPlugins = [];

    /**
     * @var array
     */
    private $sampleMetaData = [];

    /**
     * @var array
     */
    private $sampleSchemas = [];

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->factoryMock     = $this->createMock(MilexFactory::class);
        $this->helper          = new ReloadHelper($this->eventDispatcher, $this->factoryMock);

        $this->sampleMetaData = [
            'MilexPlugin\MilexZapierBundle' => [$this->createMock(ClassMetadata::class)],
            'MilexPlugin\MilexCitrixBundle' => [$this->createMock(ClassMetadata::class)],
        ];

        $sampleSchema = $this->createMock(Schema::class);
        $sampleSchema->method('getTables')
                ->willReturn([]);

        $this->sampleSchemas = [
            'MilexPlugin\MilexZapierBundle' => $sampleSchema,
            'MilexPlugin\MilexCitrixBundle' => $sampleSchema,
        ];

        $this->sampleAllPlugins = [
            'MilexZapierBundle' => [
                'isPlugin'          => true,
                'base'              => 'MilexZapier',
                'bundle'            => 'MilexZapierBundle',
                'namespace'         => 'MilexPlugin\MilexZapierBundle',
                'symfonyBundleName' => 'MilexZapierBundle',
                'bundleClass'       => 'Milex\PluginBundle\Tests\Helper\PluginBundleBaseStub',
                'permissionClasses' => [],
                'relative'          => 'plugins/MilexZapierBundle',
                'directory'         => '/Users/jan/dev/milex/plugins/MilexZapierBundle',
                'config'            => [
                    'name'        => 'Zapier Integration',
                    'description' => 'Zapier lets you connect Milex with 1100+ other apps',
                    'version'     => '1.0',
                    'author'      => 'Milex',
                ],
            ],
            'MilexCitrixBundle' => [
                'isPlugin'          => true,
                'base'              => 'MilexCitrix',
                'bundle'            => 'MilexCitrixBundle',
                'namespace'         => 'MilexPlugin\MilexCitrixBundle',
                'symfonyBundleName' => 'MilexCitrixBundle',
                'bundleClass'       => 'Milex\PluginBundle\Tests\Helper\PluginBundleBaseStub',
                'permissionClasses' => [],
                'relative'          => 'plugins/MilexCitrixBundle',
                'directory'         => '/Users/jan/dev/milex/plugins/MilexCitrixBundle',
                'config'            => [
                    'name'        => 'Citrix',
                    'description' => 'Enables integration with Milex supported Citrix collaboration products.',
                    'version'     => '1.0',
                    'author'      => 'Milex',
                    'routes'      => [
                        'public' => [
                            'milex_citrix_proxy' => [
                                'path'       => '/citrix/proxy',
                                'controller' => 'MilexCitrixBundle:Public:proxy',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testDisableMissingPlugins()
    {
        $sampleInstalledPlugins = [
            'MilexZapierBundle'  => $this->createSampleZapierPlugin(),
            'MilexHappierBundle' => $this->createSampleHappierPlugin(),
        ];

        $disabledPlugins = $this->helper->disableMissingPlugins($this->sampleAllPlugins, $sampleInstalledPlugins);

        $this->assertEquals(1, count($disabledPlugins));
        $this->assertEquals('Happier Integration', $disabledPlugins['MilexHappierBundle']->getName());
        $this->assertTrue($disabledPlugins['MilexHappierBundle']->isMissing());
    }

    public function testEnableFoundPlugins()
    {
        $zapierPlugin = $this->createSampleZapierPlugin();
        $zapierPlugin->setIsMissing(true);
        $sampleInstalledPlugins = [
            'MilexZapierBundle' => $zapierPlugin,
            'MilexCitrixBundle' => $this->createSampleCitrixPlugin(),
        ];

        $enabledPlugins = $this->helper->enableFoundPlugins($this->sampleAllPlugins, $sampleInstalledPlugins);

        $this->assertEquals(1, count($enabledPlugins));
        $this->assertEquals('Zapier Integration', $enabledPlugins['MilexZapierBundle']->getName());
        $this->assertFalse($enabledPlugins['MilexZapierBundle']->isMissing());
    }

    public function testUpdatePlugins()
    {
        $this->sampleAllPlugins['MilexZapierBundle']['config']['version']     = '1.0.1';
        $this->sampleAllPlugins['MilexZapierBundle']['config']['description'] = 'Updated description';
        $sampleInstalledPlugins                                                = [
            'MilexZapierBundle'  => $this->createSampleZapierPlugin(),
            'MilexCitrixBundle'  => $this->createSampleCitrixPlugin(),
            'MilexHappierBundle' => $this->createSampleHappierPlugin(),
        ];
        $plugin = $this->createSampleZapierPlugin();
        $plugin->setVersion('1.0.1');
        $plugin->setDescription('Updated description');
        $event = new PluginUpdateEvent($plugin, '1.0');
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event, PluginEvents::ON_PLUGIN_UPDATE);
        $updatedPlugins = $this->helper->updatePlugins($this->sampleAllPlugins, $sampleInstalledPlugins, $this->sampleMetaData, $this->sampleSchemas);

        $this->assertEquals(1, count($updatedPlugins));
        $this->assertEquals('Zapier Integration', $updatedPlugins['MilexZapierBundle']->getName());
        $this->assertEquals('1.0.1', $updatedPlugins['MilexZapierBundle']->getVersion());
        $this->assertEquals('Updated description', $updatedPlugins['MilexZapierBundle']->getDescription());
    }

    public function testInstallPlugins()
    {
        $sampleInstalledPlugins = [
            'MilexCitrixBundle'  => $this->createSampleCitrixPlugin(),
            'MilexHappierBundle' => $this->createSampleHappierPlugin(),
        ];
        $event = new PluginInstallEvent($this->createSampleZapierPlugin());
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event, PluginEvents::ON_PLUGIN_INSTALL);

        $installedPlugins = $this->helper->installPlugins($this->sampleAllPlugins, $sampleInstalledPlugins, $this->sampleMetaData, $this->sampleSchemas);

        $this->assertEquals(1, count($installedPlugins));
        $this->assertEquals('Zapier Integration', $installedPlugins['MilexZapierBundle']->getName());
        $this->assertEquals('1.0', $installedPlugins['MilexZapierBundle']->getVersion());
        $this->assertEquals('MilexZapierBundle', $installedPlugins['MilexZapierBundle']->getBundle());
        $this->assertEquals('Milex', $installedPlugins['MilexZapierBundle']->getAuthor());
        $this->assertEquals('Zapier lets you connect Milex with 1100+ other apps', $installedPlugins['MilexZapierBundle']->getDescription());
        $this->assertFalse($installedPlugins['MilexZapierBundle']->isMissing());
    }

    private function createSampleZapierPlugin()
    {
        $plugin = new Plugin();
        $plugin->setName('Zapier Integration');
        $plugin->setDescription('Zapier lets you connect Milex with 1100+ other apps');
        $plugin->isMissing(false);
        $plugin->setBundle('MilexZapierBundle');
        $plugin->setVersion('1.0');
        $plugin->setAuthor('Milex');

        return $plugin;
    }

    private function createSampleCitrixPlugin()
    {
        $plugin = new Plugin();
        $plugin->setName('Citrix');
        $plugin->setDescription('Enables integration with Milex supported Citrix collaboration products.');
        $plugin->isMissing(false);
        $plugin->setBundle('MilexCitrixBundle');
        $plugin->setVersion('1.0');
        $plugin->setAuthor('Milex');

        return $plugin;
    }

    private function createSampleHappierPlugin()
    {
        $plugin = new Plugin();
        $plugin->setName('Happier Integration');
        $plugin->setDescription('Happier lets you connect Milex with 1100+ other apps');
        $plugin->isMissing(false);
        $plugin->setBundle('MilexHappierBundle');
        $plugin->setVersion('1.0');
        $plugin->setAuthor('Milex');

        return $plugin;
    }
}
