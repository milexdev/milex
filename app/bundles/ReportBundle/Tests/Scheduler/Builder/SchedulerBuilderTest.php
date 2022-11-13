<?php

namespace Milex\ReportBundle\Tests\Scheduler\Builder;

use Milex\ReportBundle\Scheduler\Builder\SchedulerBuilder;
use Milex\ReportBundle\Scheduler\Entity\SchedulerEntity;
use Milex\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Milex\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Milex\ReportBundle\Scheduler\Factory\SchedulerTemplateFactory;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;

class SchedulerBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetNextEvent()
    {
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $schedulerBuilder         = new SchedulerBuilder($schedulerTemplateFactory);

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);

        $events = $schedulerBuilder->getNextEvent($schedulerEntity);

        $this->assertInstanceOf(RecurrenceCollection::class, $events);

        $event = $events[0];
        $this->assertInstanceOf(Recurrence::class, $event);

        $expectedDate = (new \DateTime())->setTime(0, 0)->modify('+1 day');
        $this->assertEquals($expectedDate, $event->getStart());
    }

    public function testGetNextEvents()
    {
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $schedulerBuilder         = new SchedulerBuilder($schedulerTemplateFactory);

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);

        $events = $schedulerBuilder->getNextEvents($schedulerEntity, 3);

        $this->assertInstanceOf(RecurrenceCollection::class, $events);

        $event = $events[0];
        $this->assertInstanceOf(Recurrence::class, $event);

        $expectedDate = (new \DateTime())->setTime(0, 0)->modify('+1 day');
        $this->assertEquals($expectedDate, $event->getStart());

        $event = $events[1];
        $expectedDate->modify('+1 day');
        $this->assertEquals($expectedDate, $event->getStart());

        $event = $events[2];
        $expectedDate->modify('+1 day');
        $this->assertEquals($expectedDate, $event->getStart());
    }

    public function testNoScheduler()
    {
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $schedulerBuilder         = new SchedulerBuilder($schedulerTemplateFactory);

        $SchedulerEntity = new SchedulerEntity(false, SchedulerEnum::UNIT_DAILY, null, null);

        $this->expectException(InvalidSchedulerException::class);

        $schedulerBuilder->getNextEvent($SchedulerEntity);
    }
}
