<?php

namespace Milex\ReportBundle\Tests\EventListener;

use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Entity\Scheduler;
use Milex\ReportBundle\Event\ReportScheduleSendEvent;
use Milex\ReportBundle\EventListener\SchedulerSubscriber;
use Milex\ReportBundle\Scheduler\Model\SendSchedule;

class SchedulerSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testNoEmailsProvided()
    {
        $sendScheduleMock = $this->getMockBuilder(SendSchedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $schedulerSubscriber = new SchedulerSubscriber($sendScheduleMock);

        $report                  = new Report();
        $date                    = new \DateTime();
        $scheduler               = new Scheduler($report, $date);
        $file                    = 'path-to-a-file';
        $reportScheduleSendEvent = new ReportScheduleSendEvent($scheduler, $file);

        $sendScheduleMock->expects($this->once())
            ->method('send')
            ->with($scheduler, $file);

        $schedulerSubscriber->onScheduleSend($reportScheduleSendEvent);
    }
}
