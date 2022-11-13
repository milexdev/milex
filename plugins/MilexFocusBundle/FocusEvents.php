<?php

namespace MilexPlugin\MilexFocusBundle;

/**
 * Class FocusEvents.
 *
 * Events available for MilexFocusBundle
 */
final class FocusEvents
{
    /**
     * The milex.focus_pre_save event is dispatched right before a focus is persisted.
     *
     * The event listener receives a MilexPlugin\MilexFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    const PRE_SAVE = 'milex.focus_pre_save';

    /**
     * The milex.focus_post_save event is dispatched right after a focus is persisted.
     *
     * The event listener receives a MilexPlugin\MilexFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    const POST_SAVE = 'milex.focus_post_save';

    /**
     * The milex.focus_pre_delete event is dispatched before a focus is deleted.
     *
     * The event listener receives a MilexPlugin\MilexFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    const PRE_DELETE = 'milex.focus_pre_delete';

    /**
     * The milex.focus_post_delete event is dispatched after a focus is deleted.
     *
     * The event listener receives a MilexPlugin\MilexFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    const POST_DELETE = 'milex.focus_post_delete';

    /**
     * The milex.focus_token_replacent event is dispatched after a load content.
     *
     * The event listener receives a MilexPlugin\MilexFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    const TOKEN_REPLACEMENT = 'milex.focus_token_replacement';

    /**
     * The milex.focus.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.focus.on_campaign_trigger_action';

    /**
     * The milex.focus.on_open event is dispatched when an focus is opened.
     *
     * The event listener receives a
     * MilexPlugin\MilexFocusBundle\Event\FocusOpenEvent instance.
     *
     * @var string
     */
    const FOCUS_ON_VIEW = 'milex.focus.on_view';
}
