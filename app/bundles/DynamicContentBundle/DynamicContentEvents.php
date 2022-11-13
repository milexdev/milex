<?php

namespace Milex\DynamicContentBundle;

/**
 * Class DynamicContentEvents
 * Events available for DynamicContentBundle.
 */
final class DynamicContentEvents
{
    /**
     * The milex.dwc_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * Milex\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    const TOKEN_REPLACEMENT = 'milex.dwc_token_replacement';

    /**
     * The milex.dwc_pre_save event is thrown right before a asset is persisted.
     *
     * The event listener receives a
     * Milex\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    const PRE_SAVE = 'milex.dwc_pre_save';

    /**
     * The milex.dwc_post_save event is thrown right after a asset is persisted.
     *
     * The event listener receives a
     * Milex\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    const POST_SAVE = 'milex.dwc_post_save';

    /**
     * The milex.dwc_pre_delete event is thrown prior to when a asset is deleted.
     *
     * The event listener receives a
     * Milex\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    const PRE_DELETE = 'milex.dwc_pre_delete';

    /**
     * The milex.dwc_post_delete event is thrown after a asset is deleted.
     *
     * The event listener receives a
     * Milex\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    const POST_DELETE = 'milex.dwc_post_delete';

    /**
     * The milex.category_pre_save event is thrown right before a category is persisted.
     *
     * The event listener receives a
     * Milex\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    const CATEGORY_PRE_SAVE = 'milex.category_pre_save';

    /**
     * The milex.category_post_save event is thrown right after a category is persisted.
     *
     * The event listener receives a
     * Milex\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    const CATEGORY_POST_SAVE = 'milex.category_post_save';

    /**
     * The milex.category_pre_delete event is thrown prior to when a category is deleted.
     *
     * The event listener receives a
     * Milex\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    const CATEGORY_PRE_DELETE = 'milex.category_pre_delete';

    /**
     * The milex.category_post_delete event is thrown after a category is deleted.
     *
     * The event listener receives a
     * Milex\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    const CATEGORY_POST_DELETE = 'milex.category_post_delete';

    /**
     * The milex.asset.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_DECISION = 'milex.dwc.on_campaign_trigger_decision';

    /**
     * The milex.asset.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.dwc.on_campaign_trigger_action';

    /**
     * The milex.dwc.on_contact_filters_evaluate event is fired when dynamic content's decision's
     * filters need to be evaluated.
     *
     * The event listener receives a
     * Milex\DynamicContentBundle\Event\ContactFiltersEvaluateEvent
     *
     * @var string
     */
    const ON_CONTACTS_FILTER_EVALUATE = 'milex.dwc.on_contact_filters_evaluate';
}
