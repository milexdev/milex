<?php

namespace Milex\ReportBundle\Tests\Scheduler\EventListener;

use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Event\ReportEvent;
use Milex\ReportBundle\Scheduler\EventListener\ReportSchedulerSubscriber;
use Milex\ReportBundle\Scheduler\Model\SchedulerPlanner;

class ReportSchedulerSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testOnReportSave()
    {
        $report = new Report();
        $event  = new ReportEvent($report);

        $schedulerPlanner = $this->getMockBuilder(SchedulerPlanner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $schedulerPlanner->expects($this->once())
            ->method('computeScheduler')
            ->with($report);

        $reportSchedulerSubscriber = new ReportSchedulerSubscriber($schedulerPlanner);
        $reportSchedulerSubscriber->onReportSave($event);
    }
}
