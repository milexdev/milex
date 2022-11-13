<?php

namespace Milex\SmsBundle;

/**
 * Class SmsEvents
 * Events available for SmsBundle.
 */
final class SmsEvents
{
    /**
     * The milex.sms_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * Milex\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    const TOKEN_REPLACEMENT = 'milex.sms_token_replacement';

    /**
     * The milex.sms_on_send event is thrown when a sms is sent.
     *
     * The event listener receives a
     * Milex\SmsBundle\Event\SmsSendEvent instance.
     *
     * @var string
     */
    const SMS_ON_SEND = 'milex.sms_on_send';

    /**
     * The milex.sms_pre_save event is thrown right before a sms is persisted.
     *
     * The event listener receives a
     * Milex\SmsBundle\Event\SmsEvent instance.
     *
     * @var string
     */
    const SMS_PRE_SAVE = 'milex.sms_pre_save';

    /**
     * The milex.sms_post_save event is thrown right after a sms is persisted.
     *
     * The event listener receives a
     * Milex\SmsBundle\Event\SmsEvent instance.
     *
     * @var string
     */
    const SMS_POST_SAVE = 'milex.sms_post_save';

    /**
     * The milex.sms_pre_delete event is thrown prior to when a sms is deleted.
     *
     * The event listener receives a
     * Milex\SmsBundle\Event\SmsEvent instance.
     *
     * @var string
     */
    const SMS_PRE_DELETE = 'milex.sms_pre_delete';

    /**
     * The milex.sms_post_delete event is thrown after a sms is deleted.
     *
     * The event listener receives a
     * Milex\SmsBundle\Event\SmsEvent instance.
     *
     * @var string
     */
    const SMS_POST_DELETE = 'milex.sms_post_delete';

    /**
     * The milex.sms.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.sms.on_campaign_trigger_action';

    /**
     * The milex.sms.on_reply event is dispatched when a SMS service receives a reply.
     *
     * The event listener receives a Milex\SmsBundle\Event\ReplyEvent
     *
     * @var string
     */
    const ON_REPLY = 'milex.sms.on_reply';

    /**
     * The milex.sms.on_campaign_reply event is dispatched when a SMS reply campaign decision is processed.
     *
     * The event listener receives a Milex\SmsBundle\Event\ReplyEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_REPLY = 'milex.sms.on_campaign_reply';
}
