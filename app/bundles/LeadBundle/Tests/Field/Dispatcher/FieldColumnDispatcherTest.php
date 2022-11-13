<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Field\Dispatcher;

use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Field\Dispatcher\FieldColumnDispatcher;
use Milex\LeadBundle\Field\Event\AddColumnEvent;
use Milex\LeadBundle\Field\Exception\AbortColumnCreateException;
use Milex\LeadBundle\Field\Settings\BackgroundSettings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FieldColumnDispatcherTest extends \PHPUnit\Framework\TestCase
{
    public function testNoBackground(): void
    {
        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);
        $leadField          = new LeadField();

        $backgroundSettings->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(false);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                'milex.lead_field_pre_add_column',
                $this->isInstanceOf(AddColumnEvent::class)
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $fieldColumnDispatcher->dispatchPreAddColumnEvent($leadField);
    }

    public function testStopPropagation(): void
    {
        $leadField          = new LeadField();
        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);

        $backgroundSettings->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                'milex.lead_field_pre_add_column',
                $this->callback(function ($event) {
                    /* @var AddColumnBackgroundEvent $event */
                    return $event instanceof AddColumnEvent;
                })
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $this->expectException(AbortColumnCreateException::class);
        $this->expectExceptionMessage('Column change will be processed in background job');

        $fieldColumnDispatcher->dispatchPreAddColumnEvent($leadField);
    }
}
