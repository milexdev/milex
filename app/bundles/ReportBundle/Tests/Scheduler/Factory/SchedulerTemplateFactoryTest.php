<?php

namespace Milex\ReportBundle\Tests\Scheduler\Factory;

use Milex\ReportBundle\Scheduler\Builder\SchedulerDailyBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerMonthBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerNowBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerWeeklyBuilder;
use Milex\ReportBundle\Scheduler\Entity\SchedulerEntity;
use Milex\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Milex\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use Milex\ReportBundle\Scheduler\Factory\SchedulerTemplateFactory;

class SchedulerTemplateFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testNowBuilder()
    {
        $schedulerEntity          = new SchedulerEntity(true, SchedulerEnum::UNIT_NOW, null, null);
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $builder                  = $schedulerTemplateFactory->getBuilder($schedulerEntity);

        $this->assertInstanceOf(SchedulerNowBuilder::class, $builder);
    }

    public function testDailyBuilder()
    {
        $schedulerEntity          = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $builder                  = $schedulerTemplateFactory->getBuilder($schedulerEntity);

        $this->assertInstanceOf(SchedulerDailyBuilder::class, $builder);
    }

    public function testWeeklyBuilder()
    {
        $schedulerEntity          = new SchedulerEntity(true, SchedulerEnum::UNIT_WEEKLY, null, null);
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $builder                  = $schedulerTemplateFactory->getBuilder($schedulerEntity);

        $this->assertInstanceOf(SchedulerWeeklyBuilder::class, $builder);
    }

    public function testMonthlyBuilder()
    {
        $schedulerEntity          = new SchedulerEntity(true, SchedulerEnum::UNIT_MONTHLY, null, null);
        $schedulerTemplateFactory = new SchedulerTemplateFactory();
        $builder                  = $schedulerTemplateFactory->getBuilder($schedulerEntity);

        $this->assertInstanceOf(SchedulerMonthBuilder::class, $builder);
    }

    public function testNotSupportedBuilder()
    {
        $schedulerEntity          = new SchedulerEntity(true, 'xx', null, null);
        $schedulerTemplateFactory = new SchedulerTemplateFactory();

        $this->expectException(NotSupportedScheduleTypeException::class);
        $schedulerTemplateFactory->getBuilder($schedulerEntity);
    }
}
