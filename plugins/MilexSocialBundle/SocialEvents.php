<?php

namespace MilexPlugin\MilexSocialBundle;

/**
 * Class SocialEvents.
 *
 * Events available for MilexSocialBundle
 */
final class SocialEvents
{
    /**
     * The milex.monitor_pre_save event is dispatched right before a monitor is persisted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const MONITOR_PRE_SAVE = 'milex.monitor_pre_save';

    /**
     * The milex.monitor_post_save event is dispatched right after a monitor is persisted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const MONITOR_POST_SAVE = 'milex.monitor_post_save';

    /**
     * The milex.monitor_pre_delete event is dispatched before a monitor item is deleted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const MONITOR_PRE_DELETE = 'milex.monitor_pre_delete';

    /**
     * The milex.monitor_post_delete event is dispatched after a monitor is deleted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const MONITOR_POST_DELETE = 'milex.monitor_post_delete';

    /**
     * The milex.monitor_post_process event is dispatched after a monitor is processed passing along the data gleaned.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const MONITOR_POST_PROCESS = 'milex.monitor_post_process';

    /**
     * The milex.tweet_pre_save event is dispatched right before a tweet is persisted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const TWEET_PRE_SAVE = 'milex.tweet_pre_save';

    /**
     * The milex.tweet_post_save event is dispatched right after a tweet is persisted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const TWEET_POST_SAVE = 'milex.tweet_post_save';

    /**
     * The milex.tweet_pre_delete event is dispatched before a tweet item is deleted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const TWEET_PRE_DELETE = 'milex.tweet_pre_delete';

    /**
     * The milex.tweet_post_delete event is dispatched after a tweet is deleted.
     *
     * The event listener receives a
     * MilexPlugin\MilexSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    const TWEET_POST_DELETE = 'milex.tweet_post_delete';

    /**
     * The milex.social.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.social.on_campaign_trigger_action';
}
