<?php

namespace Milex\ReportBundle\Scheduler\Factory;

use Milex\ReportBundle\Scheduler\Builder\SchedulerDailyBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerMonthBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerNowBuilder;
use Milex\ReportBundle\Scheduler\Builder\SchedulerWeeklyBuilder;
use Milex\ReportBundle\Scheduler\BuilderInterface;
use Milex\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use Milex\ReportBundle\Scheduler\SchedulerInterface;

class SchedulerTemplateFactory
{
    /**
     * @return BuilderInterface
     *
     * @throws NotSupportedScheduleTypeException
     */
    public function getBuilder(SchedulerInterface $scheduler)
    {
        if ($scheduler->isScheduledNow()) {
            return new SchedulerNowBuilder();
        }
        if ($scheduler->isScheduledDaily()) {
            return new SchedulerDailyBuilder();
        }
        if ($scheduler->isScheduledWeekly()) {
            return new SchedulerWeeklyBuilder();
        }
        if ($scheduler->isScheduledMonthly()) {
            return new SchedulerMonthBuilder();
        }

        throw new NotSupportedScheduleTypeException();
    }
}
