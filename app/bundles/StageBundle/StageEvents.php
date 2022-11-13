<?php

namespace Milex\StageBundle;

/**
 * Class StageEvents.
 *
 * Events available for StageBundle
 */
final class StageEvents
{
    /**
     * The milex.stage_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a Milex\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    const STAGE_PRE_SAVE = 'milex.stage_pre_save';

    /**
     * The milex.stage_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a Milex\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    const STAGE_POST_SAVE = 'milex.stage_post_save';

    /**
     * The milex.stage_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a Milex\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    const STAGE_PRE_DELETE = 'milex.stage_pre_delete';

    /**
     * The milex.stage_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a Milex\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    const STAGE_POST_DELETE = 'milex.stage_post_delete';

    /**
     * The milex.stage_on_build event is thrown before displaying the stage builder form to allow adding of custom actions.
     *
     * The event listener receives a Milex\StageBundle\Event\StageBuilderEvent instance.
     *
     * @var string
     */
    const STAGE_ON_BUILD = 'milex.stage_on_build';

    /**
     * The milex.stage_on_action event is thrown to execute a stage action.
     *
     * The event listener receives a Milex\StageBundle\Event\StageActionEvent instance.
     *
     * @var string
     */
    const STAGE_ON_ACTION = 'milex.stage_on_action';

    /**
     * The milex.stage.on_campaign_batch_action event is dispatched when the campaign action triggers.
     *
     * The event listener receives a Milex\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_BATCH_ACTION = 'milex.stage.on_campaign_batch_action';

    /**
     * @deprecated; use ON_CAMPAIGN_BATCH_ACTION instead
     *
     * The milex.stage.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.stage.on_campaign_trigger_action';
}
