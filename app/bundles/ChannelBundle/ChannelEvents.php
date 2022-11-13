<?php

namespace Milex\ChannelBundle;

/**
 * Class ChannelEvents.
 */
final class ChannelEvents
{
    /**
     * The milex.add_channel event registers communication channels.
     *
     * The event listener receives a Milex\ChannelBundle\Event\ChannelEvent instance.
     *
     * @var string
     */
    const ADD_CHANNEL = 'milex.add_channel';

    /**
     * The milex.channel_broadcast event is dispatched by the milex:send:broadcast command to process communication to pending contacts.
     *
     * The event listener receives a Milex\ChannelBundle\Event\ChannelBroadcastEvent instance.
     *
     * @var string
     */
    const CHANNEL_BROADCAST = 'milex.channel_broadcast';

    /**
     * The milex.message_queued event is dispatched to save a message to the queue.
     *
     * The event listener receives a Milex\ChannelBundle\Event\MessageQueueEvent instance.
     *
     * @var string
     */
    const MESSAGE_QUEUED = 'milex.message_queued';

    /**
     * The milex.process_message_queue event is dispatched to be processed by a listener.
     *
     * The event listener receives a Milex\ChannelBundle\Event\MessageQueueProcessEvent instance.
     *
     * @var string
     */
    const PROCESS_MESSAGE_QUEUE = 'milex.process_message_queue';

    /**
     * The milex.process_message_queue_batch event is dispatched to process a batch of messages by channel and channel ID.
     *
     * The event listener receives a Milex\ChannelBundle\Event\MessageQueueBatchProcessEvent instance.
     *
     * @var string
     */
    const PROCESS_MESSAGE_QUEUE_BATCH = 'milex.process_message_queue_batch';

    /**
     * The milex.channel.on_campaign_batch_action event is dispatched when the campaign action triggers.
     *
     * The event listener receives a Milex\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_BATCH_ACTION = 'milex.channel.on_campaign_batch_action';

    /**
     * The milex.message_pre_save event is dispatched right before a form is persisted.
     *
     * The event listener receives a
     * Milex\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_PRE_SAVE = 'milex.message_pre_save';

    /**
     * The milex.message_post_save event is dispatched right after a form is persisted.
     *
     * The event listener receives a
     * Milex\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_POST_SAVE = 'milex.message_post_save';

    /**
     * The milex.message_pre_delete event is dispatched before a form is deleted.
     *
     * The event listener receives a
     * Milex\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_PRE_DELETE = 'milex.message_pre_delete';

    /**
     * The milex.message_post_delete event is dispatched after a form is deleted.
     *
     * The event listener receives a
     * Milex\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_POST_DELETE = 'milex.message_post_delete';
}
