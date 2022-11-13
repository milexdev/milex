<?php

declare(strict_types=1);

namespace Milex\CampaignBundle\Tests\Executioner\Dispatcher;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Entity\LeadEventLog;
use Milex\CampaignBundle\Event\ConditionEvent;
use Milex\CampaignBundle\EventCollector\Accessor\Event\ConditionAccessor;
use Milex\CampaignBundle\Executioner\Dispatcher\ConditionDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConditionDispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|ConditionAccessor
     */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->config     = $this->createMock(ConditionAccessor::class);
    }

    public function testConditionEventIsDispatched(): void
    {
        $this->config->expects($this->once())
            ->method('getEventName')
            ->willReturn('something');

        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                ['something', $this->isInstanceOf(ConditionEvent::class)],
                [CampaignEvents::ON_EVENT_CONDITION_EVALUATION, $this->isInstanceOf(ConditionEvent::class)]
            );

        (new ConditionDispatcher($this->dispatcher))->dispatchEvent($this->config, new LeadEventLog());
    }
}
