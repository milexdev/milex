<?php

namespace MilexPlugin\MilexCrmBundle\Tests;

use Milex\PluginBundle\Tests\Integration\AbstractIntegrationTestCase;
use MilexPlugin\MilexCrmBundle\Api\DynamicsApi;
use MilexPlugin\MilexCrmBundle\Integration\DynamicsIntegration;

class DynamicsApiTest extends AbstractIntegrationTestCase
{
    /** @var DynamicsApi */
    private $api;

    /** @var DynamicsIntegration */
    private $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new DynamicsIntegration(
            $this->dispatcher,
            $this->cache,
            $this->em,
            $this->session,
            $this->request,
            $this->router,
            $this->translator,
            $this->logger,
            $this->encryptionHelper,
            $this->leadModel,
            $this->companyModel,
            $this->pathsHelper,
            $this->notificationModel,
            $this->fieldModel,
            $this->integrationEntityModel,
            $this->doNotContact
        );

        $this->api         = new DynamicsApi($this->integration);
    }

    public function testIntegration()
    {
        $this->assertSame('Dynamics', $this->integration->getName());
    }
}
