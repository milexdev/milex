<?php

namespace Milex\LeadBundle\Tests\Segment\Decorator;

use Milex\LeadBundle\Event\LeadListFiltersDecoratorDelegateEvent;
use Milex\LeadBundle\LeadEvents;
use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Decorator\BaseDecorator;
use Milex\LeadBundle\Segment\Decorator\CompanyDecorator;
use Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator;
use Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory;
use Milex\LeadBundle\Segment\Decorator\DecoratorFactory;
use Milex\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use Milex\LeadBundle\Services\ContactSegmentFilterDictionary;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DecoratorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $eventDispatcherMock;

    /**
     * @var ContactSegmentFilterDictionary
     */
    private $contactSegmentFilterDictionary;

    /**
     * @var MockObject|BaseDecorator
     */
    private $baseDecorator;

    /**
     * @var MockObject|CustomMappedDecorator
     */
    private $customMappedDecorator;

    /**
     * @var MockObject|CompanyDecorator
     */
    private $companyDecorator;

    /**
     * @var MockObject|DateOptionFactory
     */
    private $dateOptionFactory;

    /**
     * @var DecoratorFactory
     */
    private $decoratorFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcherMock            = $this->createMock(EventDispatcherInterface::class);
        $this->contactSegmentFilterDictionary = new ContactSegmentFilterDictionary($this->eventDispatcherMock);
        $this->baseDecorator                  = $this->createMock(BaseDecorator::class);
        $this->customMappedDecorator          = $this->createMock(CustomMappedDecorator::class);
        $this->companyDecorator               = $this->createMock(CompanyDecorator::class);
        $this->dateOptionFactory              = $this->createMock(DateOptionFactory::class);
        $this->decoratorFactory               = new DecoratorFactory(
            $this->contactSegmentFilterDictionary,
            $this->baseDecorator,
            $this->customMappedDecorator,
            $this->dateOptionFactory,
            $this->companyDecorator,
            $this->eventDispatcherMock);
    }

    public function testBaseDecorator(): void
    {
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'date_identified',
            'type'     => 'number',
        ]);

        $this->assertInstanceOf(
            BaseDecorator::class,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testCustomMappedDecorator(): void
    {
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'hit_url_count',
            'type'     => 'number',
        ]);

        $this->assertInstanceOf(
            CustomMappedDecorator::class,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testDateDecoratorWhenNoSubscriberProvidesDecorator(): void
    {
        $filterDecoratorInterface  = $this->createMock(FilterDecoratorInterface::class);
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate(['type' => 'date']);

        $this->dateOptionFactory->expects($this->once())
            ->method('getDateOption')
            ->with($contactSegmentFilterCrate)
            ->willReturn($filterDecoratorInterface);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                LeadEvents::SEGMENT_ON_DECORATOR_DELEGATE,
                $this->callback(
                    function (LeadListFiltersDecoratorDelegateEvent $event) use ($contactSegmentFilterCrate) {
                        $this->assertNull($event->getDecorator());
                        $this->assertSame($contactSegmentFilterCrate, $event->getCrate());

                        return true;
                    }
                )
            );

        $this->assertSame(
            $filterDecoratorInterface,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testDateDecoratorWhenSubscriberProvidesDecorator(): void
    {
        $filterDecoratorInterface  = $this->createMock(FilterDecoratorInterface::class);
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate(['type' => 'date']);

        $this->dateOptionFactory->expects($this->never())
            ->method('getDateOption');

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                LeadEvents::SEGMENT_ON_DECORATOR_DELEGATE,
                $this->callback(
                    function (LeadListFiltersDecoratorDelegateEvent $event) use ($contactSegmentFilterCrate, $filterDecoratorInterface) {
                        $this->assertNull($event->getDecorator());
                        $this->assertSame($contactSegmentFilterCrate, $event->getCrate());

                        $event->setDecorator($filterDecoratorInterface);

                        return true;
                    }
                )
            );

        $this->assertSame(
            $filterDecoratorInterface,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }
}
