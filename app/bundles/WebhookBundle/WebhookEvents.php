<?php

namespace Milex\WebhookBundle;

/**
 * Class MilexWebhookEvents
 * Events available for MilexWebhookBundle.
 */
final class WebhookEvents
{
    /**
     * The milex.webhook_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    const WEBHOOK_PRE_SAVE = 'milex.webhook_pre_save';

    /**
     * The milex.webhook_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    const WEBHOOK_POST_SAVE = 'milex.webhook_post_save';

    /**
     * The milex.webhook_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    const WEBHOOK_PRE_DELETE = 'milex.webhook_pre_delete';

    /**
     * The milex.webhook_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    const WEBHOOK_POST_DELETE = 'milex.webhook_post_delete';

    /**
     * The milex.webhook_kill event is thrown when target is not available.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookEvent instance.
     *
     * @var string
     */
    const WEBHOOK_KILL = 'milex.webhook_kill';

    /**
     * The milex.webhook_queue_on_add event is thrown as the queue entity is created, before it is persisted to the database.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookQueueEvent instance.
     *
     * @var string
     */
    const WEBHOOK_QUEUE_ON_ADD = 'milex.webhook_queue_on_add';

    /**
     * The milex.webhook_pre_execute event is thrown right before a webhook URL is executed.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookExecuteEvent instance.
     *
     * @var string
     */
    const WEBHOOK_PRE_EXECUTE = 'milex.webhook_pre_execute';

    /**
     * The milex.webhook_post_execute event is thrown right after a webhook URL is executed.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookExecuteEvent instance.
     *
     * @var string
     */
    const WEBHOOK_POST_EXECUTE = 'milex.webhook_post_execute';

    /**
     * The milex.webhook_on_build event is as the webhook form is built.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookBuild instance.
     *
     * @var string
     */
    const WEBHOOK_ON_BUILD = 'milex.webhook_on_build';

    /**
     * The milex.webhook.campaign_on_trigger event is dispatched from the milex:campaign:trigger command.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignTriggerEvent instance.
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.webhook.campaign_on_trigger_action';

    /**
     * The milex.webhook_on_request event is fired before request is processed.
     *
     * The event listener receives a Milex\WebhookBundle\Event\WebhookRequestEvent instance.
     *
     * @var string
     */
    const WEBHOOK_ON_REQUEST = 'milex.webhook_on_request';
}
