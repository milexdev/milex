<?php

namespace Milex\PluginBundle\Tests;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Helper\BundleHelper;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\PathsHelper;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\PluginBundle\Entity\IntegrationEntityRepository;
use Milex\PluginBundle\Entity\IntegrationRepository;
use Milex\PluginBundle\Entity\PluginRepository;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Milex\PluginBundle\Integration\AbstractIntegration;
use Milex\PluginBundle\Model\PluginModel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigFormTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testConfigForm()
    {
        $plugins = $this->getIntegrationObject()->getIntegrationObjects();

        foreach ($plugins as $name => $s) {
            $featureSettings = $s->getFormSettings();

            $this->assertArrayHasKey('requires_callback', $featureSettings);
            $this->assertArrayHasKey('requires_authorization', $featureSettings);
            if ($featureSettings['requires_callback']) {
                $this->assertNotEmpty($s->getAuthCallbackUrl());
            }
        }
    }

    public function testOauth()
    {
        $plugins    = $this->getIntegrationObject()->getIntegrationObjects();
        $url        = 'https://test.com';
        $parameters = ['a' => 'testa', 'b' => 'testb'];
        $method     = 'GET';
        $authType   = 'oauth2';
        $expected   = [
            [
              'a' => 'testa',
              'b' => 'testb',
              ''  => '',
            ], [
              'oauth-token: ',
              'Authorization: OAuth ',
            ],
        ];

        /** @var AbstractIntegration $integration */
        foreach ($plugins as $integration) {
            $this->assertSame($expected, $integration->prepareRequest($url, $parameters, $method, [], $authType));
        }
    }

    public function testAmendLeadDataBeforeMilexPopulate()
    {
        $plugins = $this->getIntegrationObject()->getIntegrationObjects();
        $object  = 'company';
        $data    = ['company_name' => 'company_name', 'email' => 'company_email'];

        /** @var AbstractIntegration $integration */
        foreach ($plugins as $integration) {
            $methodExists = method_exists($integration, 'amendLeadDataBeforeMilexPopulate');
            if ($methodExists) {
                $count = $integration->amendLeadDataBeforeMilexPopulate($data, $object);
                $this->assertGreaterThanOrEqual(0, $count);
            } else {
                $this->assertFalse($methodExists, 'To make this test avoid the risky waring...');
            }
        }
    }

    public function getIntegrationObject()
    {
        //create an integration object
        $pathsHelper          = $this->getMockBuilder(PathsHelper::class)->disableOriginalConstructor()->getMock();
        $bundleHelper         = $this->getMockBuilder(BundleHelper::class)->disableOriginalConstructor()->getMock();
        $pluginModel          = $this->getMockBuilder(PluginModel::class)->disableOriginalConstructor()->getMock();
        $coreParametersHelper = new CoreParametersHelper(self::$kernel->getContainer());
        $templatingHelper     = $this->getMockBuilder(TemplatingHelper::class)->disableOriginalConstructor()->getMock();
        $entityManager        = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginRepository = $this
            ->getMockBuilder(PluginRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registeredPluginBundles = self::$container->getParameter('milex.plugin.bundles');
        $milexPlugins           = self::$container->getParameter('milex.bundles');
        $bundleHelper->expects($this->any())->method('getPluginBundles')->willReturn([$registeredPluginBundles]);

        $bundleHelper->expects($this->any())->method('getMilexBundles')->willReturn(array_merge($milexPlugins, $registeredPluginBundles));
        $integrationEntityRepository = $this
            ->getMockBuilder(IntegrationEntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $integrationRepository = $this
            ->getMockBuilder(IntegrationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->expects($this
            ->any())
                ->method('getRepository')
                ->will(
                    $this->returnValueMap(
                            [
                                ['MilexPluginBundle:Plugin', $pluginRepository],
                                ['MilexPluginBundle:Integration', $integrationRepository],
                                ['MilexPluginBundle:IntegrationEntity', $integrationEntityRepository],
                            ]
                    )
                );

        $integrationHelper = new IntegrationHelper(
            self::$kernel->getContainer(),
            $entityManager,
            $pathsHelper,
            $bundleHelper,
            $coreParametersHelper,
            $templatingHelper,
            $pluginModel
            );

        return $integrationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
