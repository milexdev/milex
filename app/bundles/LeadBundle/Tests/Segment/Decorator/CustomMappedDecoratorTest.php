<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator;

use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\ContactSegmentFilterOperator;
use Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator;
use Milex\LeadBundle\Services\ContactSegmentFilterDictionary;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomMappedDecoratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator::getField
     */
    public function testGetField(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'lead_email_read_count',
        ]);

        $this->assertSame('open_count', $customMappedDecorator->getField($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator::getTable
     */
    public function testGetTable(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'lead_email_read_count',
        ]);

        $this->assertSame(MILEX_TABLE_PREFIX.'email_stats', $customMappedDecorator->getTable($contactSegmentFilterCrate));
    }

    /**
     * @covers \Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator::getQueryType
     */
    public function testGetQueryType(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'dnc_bounced',
        ]);

        $this->assertSame('milex.lead.query.builder.special.dnc', $customMappedDecorator->getQueryType($contactSegmentFilterCrate));
    }

    /**
     * @return CustomMappedDecorator
     */
    private function getDecorator()
    {
        $contactSegmentFilterOperator   = $this->createMock(ContactSegmentFilterOperator::class);
        $dispatcherMock                 = $this->createMock(EventDispatcherInterface::class);
        $contactSegmentFilterDictionary = new ContactSegmentFilterDictionary($dispatcherMock);

        return new CustomMappedDecorator($contactSegmentFilterOperator, $contactSegmentFilterDictionary);
    }
}
