<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator\Date\Other;

use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\Date\DateOptionParameters;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary;
use Milex\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use Milex\LeadBundle\Segment\Decorator\DateDecorator;

class DateAnniversaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary::getOperator
     */
    public function testGetOperator()
    {
        $dateDecorator             = $this->createMock(DateDecorator::class);
        $timezoneResolver          = $this->createMock(TimezoneResolver::class);

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([]);

        $filterDecorator = new DateAnniversary($dateDecorator, $dateOptionParameters);

        $this->assertEquals('like', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary::getParameterValue
     */
    public function testGetParameterValue()
    {
        /**
         * Today in '%-m-d' format.
         *
         * @var string
         */
        $expectedResult = '%'.(new \DateTime(null, new \DateTimeZone('UTC')))->format('-m-d');

        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $timezoneResolver->method('getDefaultDate')
            ->with(false)
            ->willReturn(
                new DateTimeHelper(
                    new \DateTime('midnight today', new \DateTimeZone('UTC')), null, 'UTC')
            );

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([]);

        $filterDecorator = new DateAnniversary($dateDecorator, $dateOptionParameters);

        $this->assertEquals($expectedResult, $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary::getParameterValue
     */
    public function testGetParameterValueWithRelativeDate()
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filter        = [
            'filter'   => 'birthday +2days',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);

        $filterDecorator = new DateAnniversary($dateDecorator, $dateOptionParameters);

        $this->assertEquals('%-03-04', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }
}
