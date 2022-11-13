<?php

namespace Milex\PageBundle;

/**
 * Class PageEvents.
 *
 * Events available for PageBundle
 */
final class PageEvents
{
    /**
     * The milex.video_on_hit event is thrown when a public page is browsed and a hit recorded in the analytics table.
     *
     * The event listener receives a Milex\PageBundle\Event\VideoHitEvent instance.
     *
     * @var string
     */
    const VIDEO_ON_HIT = 'milex.video_on_hit';

    /**
     * The milex.page_on_hit event is thrown when a public page is browsed and a hit recorded in the analytics table.
     *
     * The event listener receives a Milex\PageBundle\Event\PageHitEvent instance.
     *
     * @var string
     */
    const PAGE_ON_HIT = 'milex.page_on_hit';

    /**
     * The milex.page_on_build event is thrown before displaying the page builder form to allow adding of tokens.
     *
     * The event listener receives a Milex\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    const PAGE_ON_BUILD = 'milex.page_on_build';

    /**
     * The milex.page_on_display event is thrown before displaying the page content.
     *
     * The event listener receives a Milex\PageBundle\Event\PageDisplayEvent instance.
     *
     * @var string
     */
    const PAGE_ON_DISPLAY = 'milex.page_on_display';

    /**
     * The milex.page_pre_save event is thrown right before a page is persisted.
     *
     * The event listener receives a Milex\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    const PAGE_PRE_SAVE = 'milex.page_pre_save';

    /**
     * The milex.page_post_save event is thrown right after a page is persisted.
     *
     * The event listener receives a Milex\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    const PAGE_POST_SAVE = 'milex.page_post_save';

    /**
     * The milex.page_pre_delete event is thrown prior to when a page is deleted.
     *
     * The event listener receives a Milex\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    const PAGE_PRE_DELETE = 'milex.page_pre_delete';

    /**
     * The milex.page_post_delete event is thrown after a page is deleted.
     *
     * The event listener receives a Milex\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    const PAGE_POST_DELETE = 'milex.page_post_delete';

    /**
     * The milex.redirect_do_not_track event is thrown when converting email links to trackables/redirectables in order to compile of list of tokens/URLs
     * to ignore.
     *
     * The event listener receives a Milex\PageBundle\Event\UntrackableUrlsEvent instance.
     *
     * @var string
     */
    const REDIRECT_DO_NOT_TRACK = 'milex.redirect_do_not_track';

    /**
     * The milex.page.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_DECISION = 'milex.page.on_campaign_trigger_decision';

    /**
     * The milex.page.on_campaign_trigger_action event is fired when the campaign action fired.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.page.on_campaign_trigger_action';

    /**
     * The milex.page.on_redirect_generate event is fired when generating a redirect.
     *
     * The event listener receives a
     * Milex\PageBundle\Event\RedirectGenerationEvent
     */
    const ON_REDIRECT_GENERATE = 'milex.page.on_redirect_generate';

    /**
     * The milex.page.on_bounce_rate_winner event is fired when there is a need to determine bounce rate winner.
     *
     * The event listener receives a
     * Milex\CoreBundle\Event\DetermineWinnerEvent
     *
     * @var string
     */
    const ON_DETERMINE_BOUNCE_RATE_WINNER = 'milex.page.on_bounce_rate_winner';

    /**
     * The milex.page.on_dwell_time_winner event is fired when there is a need to determine a winner based on dwell time.
     *
     * The event listener receives a
     * Milex\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    const ON_DETERMINE_DWELL_TIME_WINNER = 'milex.page.on_dwell_time_winner';

    /**
     * The milex.page.on_contact_tracked event is dispatched when a contact is tracked via the mt() tracking event.
     *
     * The event listener receives a
     * Milex\PageBundle\Event\TrackingEvent
     */
    const ON_CONTACT_TRACKED = 'milex.page.on_contact_tracked';
}
