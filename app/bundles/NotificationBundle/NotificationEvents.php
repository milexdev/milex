<?php

namespace Milex\NotificationBundle;

/**
 * Class NotificationEvents
 * Events available for NotificationBundle.
 */
final class NotificationEvents
{
    /**
     * The milex.notification_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * Milex\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    const TOKEN_REPLACEMENT = 'milex.notification_token_replacement';

    /**
     * The milex.notification_form_action_send event is thrown when a notification is sent
     * as part of a form action.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\SendingNotificationEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_ON_FORM_ACTION_SEND = 'milex.notification_form_action_send';

    /**
     * The milex.notification_on_send event is thrown when a notification is sent.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\NotificationSendEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_ON_SEND = 'milex.notification_on_send';

    /**
     * The milex.notification_pre_save event is thrown right before a notification is persisted.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_PRE_SAVE = 'milex.notification_pre_save';

    /**
     * The milex.notification_post_save event is thrown right after a notification is persisted.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_POST_SAVE = 'milex.notification_post_save';

    /**
     * The milex.notification_pre_delete event is thrown prior to when a notification is deleted.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_PRE_DELETE = 'milex.notification_pre_delete';

    /**
     * The milex.notification_post_delete event is thrown after a notification is deleted.
     *
     * The event listener receives a
     * Milex\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    const NOTIFICATION_POST_DELETE = 'milex.notification_post_delete';

    /**
     * The milex.notification.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.notification.on_campaign_trigger_action';

    /**
     * The milex.notification.on_campaign_trigger_condition event is fired when the campaign condition triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_CONDITION = 'milex.notification.on_campaign_trigger_notification';
}
