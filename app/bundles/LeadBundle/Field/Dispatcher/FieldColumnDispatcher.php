<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Field\Dispatcher;

use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Field\Event\AddColumnEvent;
use Milex\LeadBundle\Field\Exception\AbortColumnCreateException;
use Milex\LeadBundle\Field\Settings\BackgroundSettings;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FieldColumnDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var BackgroundSettings
     */
    private $backgroundSettings;

    public function __construct(EventDispatcherInterface $dispatcher, BackgroundSettings $backgroundSettings)
    {
        $this->dispatcher         = $dispatcher;
        $this->backgroundSettings = $backgroundSettings;
    }

    /**
     * @throws AbortColumnCreateException
     */
    public function dispatchPreAddColumnEvent(LeadField $leadField): void
    {
        $shouldProcessInBackground = $this->backgroundSettings->shouldProcessColumnChangeInBackground();
        $event                     = new AddColumnEvent($leadField, $shouldProcessInBackground);

        $this->dispatcher->dispatch(LeadEvents::LEAD_FIELD_PRE_ADD_COLUMN, $event);

        if ($shouldProcessInBackground) {
            throw new AbortColumnCreateException('Column change will be processed in background job');
        }
    }
}
