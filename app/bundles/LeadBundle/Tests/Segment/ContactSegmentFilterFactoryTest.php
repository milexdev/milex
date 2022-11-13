<?php

namespace Milex\LeadBundle\Tests\Segment;

use Milex\LeadBundle\Entity\LeadList;
use Milex\LeadBundle\Segment\ContactSegmentFilterFactory;
use Milex\LeadBundle\Segment\ContactSegmentFilters;
use Milex\LeadBundle\Segment\Decorator\DecoratorFactory;
use Milex\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use Milex\LeadBundle\Segment\Query\Filter\FilterQueryBuilderInterface;
use Milex\LeadBundle\Segment\TableSchemaColumnsCache;
use Symfony\Component\DependencyInjection\Container;

class ContactSegmentFilterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Milex\LeadBundle\Segment\ContactSegmentFilterFactory
     */
    public function testLeadFilter()
    {
        $tableSchemaColumnsCache = $this->createMock(TableSchemaColumnsCache::class);
        $container               = $this->createMock(Container::class);
        $decoratorFactory        = $this->createMock(DecoratorFactory::class);

        $filterDecorator = $this->createMock(FilterDecoratorInterface::class);
        $decoratorFactory->expects($this->exactly(3))
            ->method('getDecoratorForFilter')
            ->willReturn($filterDecorator);

        $filterDecorator->expects($this->exactly(3))
            ->method('getQueryType')
            ->willReturn('MyQueryTypeId');

        $filterQueryBuilder = $this->createMock(FilterQueryBuilderInterface::class);
        $container->expects($this->exactly(3))
            ->method('get')
            ->with('MyQueryTypeId')
            ->willReturn($filterQueryBuilder);

        $contactSegmentFilterFactory = new ContactSegmentFilterFactory($tableSchemaColumnsCache, $container, $decoratorFactory);

        $leadList = new LeadList();
        $leadList->setFilters([
            [
                'glue'     => 'and',
                'field'    => 'date_identified',
                'object'   => 'lead',
                'type'     => 'datetime',
                'filter'   => null,
                'display'  => null,
                'operator' => '!empty',
            ],
            [
                'glue'     => 'and',
                'type'     => 'text',
                'field'    => 'hit_url',
                'operator' => 'like',
                'filter'   => 'test.com',
                'display'  => '',
            ],
            [
                'glue'     => 'or',
                'type'     => 'lookup',
                'field'    => 'state',
                'operator' => '=',
                'filter'   => 'QLD',
                'display'  => '',
            ],
        ]);

        $contactSegmentFilters = $contactSegmentFilterFactory->getSegmentFilters($leadList);

        $this->assertInstanceOf(ContactSegmentFilters::class, $contactSegmentFilters);
        $this->assertCount(3, $contactSegmentFilters);
    }
}
