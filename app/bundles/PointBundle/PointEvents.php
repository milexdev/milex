<?php

namespace Milex\PointBundle;

/**
 * Class PointEvents.
 *
 * Events available for PointBundle
 */
final class PointEvents
{
    /**
     * The milex.point_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a Milex\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    const POINT_PRE_SAVE = 'milex.point_pre_save';

    /**
     * The milex.point_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a Milex\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    const POINT_POST_SAVE = 'milex.point_post_save';

    /**
     * The milex.point_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a Milex\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    const POINT_PRE_DELETE = 'milex.point_pre_delete';

    /**
     * The milex.point_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a Milex\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    const POINT_POST_DELETE = 'milex.point_post_delete';

    /**
     * The milex.point_on_build event is thrown before displaying the point builder form to allow adding of custom actions.
     *
     * The event listener receives a Milex\PointBundle\Event\PointBuilderEvent instance.
     *
     * @var string
     */
    const POINT_ON_BUILD = 'milex.point_on_build';

    /**
     * The milex.point_on_action event is thrown to execute a point action.
     *
     * The event listener receives a Milex\PointBundle\Event\PointActionEvent instance.
     *
     * @var string
     */
    const POINT_ON_ACTION = 'milex.point_on_action';

    /**
     * The milex.point_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    const TRIGGER_PRE_SAVE = 'milex.trigger_pre_save';

    /**
     * The milex.trigger_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    const TRIGGER_POST_SAVE = 'milex.trigger_post_save';

    /**
     * The milex.trigger_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    const TRIGGER_PRE_DELETE = 'milex.trigger_pre_delete';

    /**
     * The milex.trigger_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    const TRIGGER_POST_DELETE = 'milex.trigger_post_delete';

    /**
     * The milex.trigger_on_build event is thrown before displaying the trigger builder form to allow adding of custom actions.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerBuilderEvent instance.
     *
     * @var string
     */
    const TRIGGER_ON_BUILD = 'milex.trigger_on_build';

    /**
     * The milex.trigger_on_event_execute event is thrown to execute a trigger event.
     *
     * The event listener receives a Milex\PointBundle\Event\TriggerExecutedEvent instance.
     *
     * @var string
     */
    const TRIGGER_ON_EVENT_EXECUTE = 'milex.trigger_on_event_execute';
}
