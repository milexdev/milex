<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator\Date\Other;

use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\Date\Other\DateDefault;
use Milex\LeadBundle\Segment\Decorator\DateDecorator;

class DateDefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\Date\Other\DateDefault::getParameterValue
     */
    public function testGetParameterValue()
    {
        $dateDecorator             = $this->createMock(DateDecorator::class);
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([]);

        $filterDecorator = new DateDefault($dateDecorator, '2018-03-02 01:02:03');

        $this->assertEquals('2018-03-02 01:02:03', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }
}
