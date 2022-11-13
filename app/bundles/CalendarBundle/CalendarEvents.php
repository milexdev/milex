<?php

namespace Milex\CalendarBundle;

/**
 * Class CalendarEvents.
 *
 * Events available for CalendarBundle
 */
final class CalendarEvents
{
    /**
     * The milex.calendar_on_generate event is thrown when generating a calendar view.
     *
     * The event listener receives a Milex\CalendarBundle\Event\CalendarGeneratorEvent instance.
     *
     * @var string
     */
    const CALENDAR_ON_GENERATE = 'milex.calendar_on_generate';

    /**
     * The milex.calendar_event_on_generate event is thrown when generating a calendar edit / new view.
     *
     * The event listener receives a Milex\CalendarBundle\Event\EventGeneratorEvent instance.
     *
     * @var string
     */
    const CALENDAR_EVENT_ON_GENERATE = 'milex.calendar_event_on_generate';
}
