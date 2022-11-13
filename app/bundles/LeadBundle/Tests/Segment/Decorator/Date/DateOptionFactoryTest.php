<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator\Date;

use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory;
use Milex\LeadBundle\Segment\Decorator\Date\Day\DateDayToday;
use Milex\LeadBundle\Segment\Decorator\Date\Day\DateDayTomorrow;
use Milex\LeadBundle\Segment\Decorator\Date\Day\DateDayYesterday;
use Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthLast;
use Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthNext;
use Milex\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateDefault;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval;
use Milex\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use Milex\LeadBundle\Segment\Decorator\Date\Week\DateWeekLast;
use Milex\LeadBundle\Segment\Decorator\Date\Week\DateWeekNext;
use Milex\LeadBundle\Segment\Decorator\Date\Week\DateWeekThis;
use Milex\LeadBundle\Segment\Decorator\Date\Year\DateYearLast;
use Milex\LeadBundle\Segment\Decorator\Date\Year\DateYearNext;
use Milex\LeadBundle\Segment\Decorator\Date\Year\DateYearThis;
use Milex\LeadBundle\Segment\Decorator\DateDecorator;
use Milex\LeadBundle\Segment\RelativeDate;

class DateOptionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testBirthday()
    {
        $filterName = 'birthday';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateAnniversary::class, $filterDecorator);

        $filterName = 'anniversary';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateAnniversary::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testDayToday()
    {
        $filterName = 'today';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayToday::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testDayTomorrow()
    {
        $filterName = 'tomorrow';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayTomorrow::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testDayYesterday()
    {
        $filterName = 'yesterday';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayYesterday::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testWeekLast()
    {
        $filterName = 'last week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekLast::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testWeekNext()
    {
        $filterName = 'next week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekNext::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testWeekThis()
    {
        $filterName = 'this week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekThis::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testMonthLast()
    {
        $filterName = 'last month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthLast::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testMonthNext()
    {
        $filterName = 'next month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthNext::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testMonthThis()
    {
        $filterName = 'this month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthThis::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testYearLast()
    {
        $filterName = 'last year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearLast::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testYearNext()
    {
        $filterName = 'next year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearNext::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testYearThis()
    {
        $filterName = 'this year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearThis::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testRelativePlus()
    {
        $filterName = '+20 days';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testRelativeMinus()
    {
        $filterName = '+20 days';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testRelativeAgo()
    {
        $filterName = '20 days ago';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testDateDefault()
    {
        $filterName = '2018-01-01';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDefault::class, $filterDecorator);
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::getDateOption
     */
    public function testNullValue()
    {
        $filterName = null;

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDefault::class, $filterDecorator);
    }

    /**
     * @param string $filterName
     *
     * @return \Milex\LeadBundle\Segment\Decorator\FilterDecoratorInterface
     */
    private function getFilterDecorator($filterName)
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $relativeDate     = $this->createMock(RelativeDate::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $relativeDate->method('getRelativeDateStrings')
            ->willReturn(
                [
                    'milex.lead.list.month_last'  => 'last month',
                    'milex.lead.list.month_next'  => 'next month',
                    'milex.lead.list.month_this'  => 'this month',
                    'milex.lead.list.today'       => 'today',
                    'milex.lead.list.tomorrow'    => 'tomorrow',
                    'milex.lead.list.yesterday'   => 'yesterday',
                    'milex.lead.list.week_last'   => 'last week',
                    'milex.lead.list.week_next'   => 'next week',
                    'milex.lead.list.week_this'   => 'this week',
                    'milex.lead.list.year_last'   => 'last year',
                    'milex.lead.list.year_next'   => 'next year',
                    'milex.lead.list.year_this'   => 'this year',
                    'milex.lead.list.birthday'    => 'birthday',
                    'milex.lead.list.anniversary' => 'anniversary',
                ]
            );

        $dateOptionFactory = new DateOptionFactory($dateDecorator, $relativeDate, $timezoneResolver);

        $filter                    = [
            'glue'     => 'and',
            'type'     => 'datetime',
            'object'   => 'lead',
            'field'    => 'date_identified',
            'operator' => '=',
            'filter'   => $filterName,
            'display'  => null,
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);

        return $dateOptionFactory->getDateOption($contactSegmentFilterCrate);
    }
}
