<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator\Date\Month;

use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\Date\DateOptionParameters;
use Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis;
use Milex\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use Milex\LeadBundle\Segment\Decorator\DateDecorator;

class DateMonthThisTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis::getOperator
     */
    public function testGetOperatorBetween()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateMonthThis($dateDecorator, $dateOptionParameters);

        $this->assertEquals('like', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis::getOperator
     */
    public function testGetOperatorLessOrEqual()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $dateDecorator->method('getOperator')
            ->with()
            ->willReturn('=<');

        $filter        = [
            'operator' => 'lte',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateMonthThis($dateDecorator, $dateOptionParameters);

        $this->assertEquals('=<', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis::getParameterValue
     */
    public function testGetParameterValueBetween()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter        = [
            'operator' => '!=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateMonthThis($dateDecorator, $dateOptionParameters);

        $expectedDate = new \DateTime('first day of this month');

        $this->assertEquals($expectedDate->format('Y-m-%'), $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis::getParameterValue
     */
    public function testGetParameterValueSingle()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter        = [
            'operator' => 'lt',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateMonthThis($dateDecorator, $dateOptionParameters);

        $expectedDate = new \DateTime('first day of this month');

        $this->assertEquals($expectedDate->format('Y-m-d'), $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }
}
