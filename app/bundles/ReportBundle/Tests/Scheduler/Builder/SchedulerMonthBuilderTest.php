<?php

namespace Milex\ReportBundle\Tests\Scheduler\Builder;

use Milex\ReportBundle\Scheduler\Builder\SchedulerMonthBuilder;
use Milex\ReportBundle\Scheduler\Entity\SchedulerEntity;
use Milex\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Milex\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Recurr\Exception\InvalidArgument;
use Recurr\Rule;

class SchedulerMonthBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuilEvent()
    {
        $schedulerDailyBuilder = new SchedulerMonthBuilder();

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, SchedulerEnum::DAY_MO, SchedulerEnum::MONTH_FREQUENCY_FIRST);

        $startDate = (new \DateTime())->setTime(0, 0)->modify('+1 day');
        $rule      = new Rule();
        $rule->setStartDate($startDate)
            ->setCount(1);

        $schedulerDailyBuilder->build($rule, $schedulerEntity);

        $this->assertEquals(Rule::$freqs['MONTHLY'], $rule->getFreq());
        $this->assertEquals(['1MO'], $rule->getByDay());
    }

    public function testBuilEventFails()
    {
        $schedulerDailyBuilder = new SchedulerMonthBuilder();

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);

        $rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects($this->once())
            ->method('setFreq')
            ->with('MONTHLY')
            ->willThrowException(new InvalidArgument());

        $this->expectException(InvalidSchedulerException::class);

        $schedulerDailyBuilder->build($rule, $schedulerEntity);
    }
}
