<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator\Date\Other;

use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\Date\DateOptionParameters;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval;
use Milex\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use Milex\LeadBundle\Segment\Decorator\DateDecorator;

class DateRelativeIntervalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getOperator
     */
    public function testGetOperatorEqual()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '+5 days', $dateOptionParameters);

        $this->assertEquals('like', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getOperator
     */
    public function testGetOperatorNotEqual()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $filter        = [
            'operator' => '!=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '+5 days', $dateOptionParameters);

        $this->assertEquals('notLike', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getOperator
     */
    public function testGetOperatorLessOrEqual()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $dateDecorator->method('getOperator')
            ->with()
            ->willReturn('==<<'); //Test that value is really returned from Decorator

        $filter        = [
            'operator' => '=<',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '+5 days', $dateOptionParameters);

        $this->assertEquals('==<<', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getParameterValue
     */
    public function testGetParameterValuePlusDaysWithGreaterOperator()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter = [
            'operator' => '>',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '+5 days', $dateOptionParameters);

        $this->assertEquals('2018-03-07', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getParameterValue
     */
    public function testGetParameterValueMinusMonthWithNotEqualOperator()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter = [
            'operator' => '!=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '-3 months', $dateOptionParameters);

        $this->assertEquals('2017-12-02%', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getParameterValue
     */
    public function testGetParameterValueDaysAgoWithNotEqualOperator()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter = [
            'operator' => '!=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '5 days ago', $dateOptionParameters);

        $this->assertEquals('2018-02-25%', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getParameterValue
     */
    public function testGetParameterValueYearsAgoWithGreaterOperator()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter = [
            'operator' => '>',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '2 years ago', $dateOptionParameters);

        $this->assertEquals('2016-03-02', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval::getParameterValue
     */
    public function testGetParameterValueDaysWithEqualOperator()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateRelativeInterval($dateDecorator, '5 days', $dateOptionParameters);

        $this->assertEquals('2018-03-07%', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }
}
