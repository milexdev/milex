<?php

namespace Milex\FormBundle;

/**
 * Class FormEvents.
 *
 * Events available for FormBundle
 */
final class FormEvents
{
    /**
     * The milex.form_pre_save event is dispatched right before a form is persisted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    const FORM_PRE_SAVE = 'milex.form_pre_save';

    /**
     * The milex.form_post_save event is dispatched right after a form is persisted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    const FORM_POST_SAVE = 'milex.form_post_save';

    /**
     * The milex.form_pre_delete event is dispatched before a form is deleted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    const FORM_PRE_DELETE = 'milex.form_pre_delete';

    /**
     * The milex.form_post_delete event is dispatched after a form is deleted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    const FORM_POST_DELETE = 'milex.form_post_delete';

    /**
     * The milex.field_pre_save event is dispatched right before a field is persisted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    const FIELD_PRE_SAVE = 'milex.field_pre_save';

    /**
     * The milex.field_post_save event is dispatched right after a field is persisted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    const FIELD_POST_SAVE = 'milex.field_post_save';

    /**
     * The milex.field_pre_delete event is dispatched before a field is deleted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    const FIELD_PRE_DELETE = 'milex.field_pre_delete';

    /**
     * The milex.field_post_delete event is dispatched after a field is deleted.
     *
     * The event listener receives a Milex\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    const FIELD_POST_DELETE = 'milex.field_post_delete';

    /**
     * The milex.form_on_build event is dispatched before displaying the form builder form to allow adding of custom form
     * fields and submit actions.
     *
     * The event listener receives a Milex\FormBundle\Event\FormBuilderEvent instance.
     *
     * @var string
     */
    const FORM_ON_BUILD = 'milex.form_on_build';

    /**
     * The milex.on_form_validate event is dispatched when a form is validated.
     *
     * The event listener receives a Milex\FormBundle\Event\ValidationEvent instance.
     *
     * @var string
     */
    const ON_FORM_VALIDATE = 'milex.on_form_validate';

    /**
     * The milex.form_on_submit event is dispatched when a new submission is fired.
     *
     * The event listener receives a Milex\FormBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const FORM_ON_SUBMIT = 'milex.form_on_submit';

    /**
     * The milex.form.on_campaign_trigger_condition event is fired when the campaign condition triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_CONDITION = 'milex.form.on_campaign_trigger_condition';

    /**
     * The milex.form.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_DECISION = 'milex.form.on_campaign_trigger_decision';

    /**
     * The milex.form.on_execute_submit_action event is dispatched to excecute the form submit actions.
     *
     * The event listener receives a
     * Milex\FormBundle\Event\SubmissionEvent
     *
     * @var string
     */
    const ON_EXECUTE_SUBMIT_ACTION = 'milex.form.on_execute_submit_action';

    /**
     * The milex.form.on_submission_rate_winner event is fired when there is a need to determine submission rate winner.
     *
     * The event listener receives a
     * Milex\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    const ON_DETERMINE_SUBMISSION_RATE_WINNER = 'milex.form.on_submission_rate_winner';
}
