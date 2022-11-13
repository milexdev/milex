<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\LeadBundle\EventListener\ConfigSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigSubscriberTest extends TestCase
{
    private ConfigSubscriber $configSubscriber;

    /**
     * @var ConfigBuilderEvent&MockObject
     */
    private $configBuilderEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configSubscriber   = new ConfigSubscriber();
        $this->configBuilderEvent = $this->createMock(ConfigBuilderEvent::class);
    }

    public function testSubscribedEvents(): void
    {
        $subscribedEvents = ConfigSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(ConfigEvents::CONFIG_ON_GENERATE, $subscribedEvents);
    }

    public function testThatWeAreAddingFormsToTheConfig(): void
    {
        $leadConfig = [
            'bundle'     => 'LeadBundle',
            'formAlias'  => 'leadconfig',
            'formType'   => 'Milex\\LeadBundle\\Form\\Type\\ConfigType',
            'formTheme'  => 'MilexLeadBundle:FormTheme\\Config',
            'parameters' => null,
        ];

        $segmentConfig = [
            'bundle'     => 'LeadBundle',
            'formAlias'  => 'segment_config',
            'formType'   => 'Milex\\LeadBundle\\Form\\Type\\SegmentConfigType',
            'formTheme'  => 'MilexLeadBundle:FormTheme\\Config',
            'parameters' => null,
        ];

        $this->configBuilderEvent
            ->expects($this->exactly(2))
            ->method('addForm')
            ->withConsecutive([$leadConfig], [$segmentConfig]);

        $this->configSubscriber->onConfigGenerate($this->configBuilderEvent);
    }
}
