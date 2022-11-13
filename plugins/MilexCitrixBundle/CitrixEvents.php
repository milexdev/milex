<?php

namespace MilexPlugin\MilexCitrixBundle;

/**
 * Class CitrixEvents.
 *
 * Events available for MilexCitrixBundle
 */
final class CitrixEvents
{
    /**
     * The milex.on_citrix_form_validate_action event is dispatched when a form is validated.
     *
     * The event listener receives a Milex\FormBundle\Event\ValidationEvent instance.
     *
     * @var string
     */
    const ON_FORM_VALIDATE_ACTION = 'milex.on_citrix_form_validate_action';

    /**
     * The milex.on_citrix_webinar_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_WEBINAR_EVENT = 'milex.on_citrix_webinar_event';

    /**
     * The milex.on_citrix_meeting_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_MEETING_EVENT = 'milex.on_citrix_meeting_event';

    /**
     * The milex.on_citrix_training_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_TRAINING_EVENT = 'milex.on_citrix_training_event';

    /**
     * The milex.on_citrix_assist_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_ASSIST_EVENT = 'milex.on_citrix_assist_event';

    /**
     * The milex.on_citrix_webinar_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_WEBINAR_ACTION = 'milex.on_citrix_webinar_action';

    /**
     * The milex.on_citrix_meeting_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_MEETING_ACTION = 'milex.on_citrix_meeting_action';

    /**
     * The milex.on_citrix_training_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_TRAINING_ACTION = 'milex.on_citrix_training_action';

    /**
     * The milex.on_citrix_assist_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Milex\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_ASSIST_ACTION = 'milex.on_citrix_assist_action';

    /**
     * The milex.on_webinar_register_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Milex\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const ON_WEBINAR_REGISTER_ACTION = 'milex.on_webinar_register_action';

    /**
     * The milex.on_meeting_start_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Milex\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const ON_MEETING_START_ACTION = 'milex.on_meeting_start_action';

    /**
     * The milex.on_training_register_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Milex\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const ON_TRAINING_REGISTER_ACTION = 'milex.on_training_register_action';

    /**
     * The milex.on_training_start_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Milex\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const ON_TRAINING_START_ACTION = 'milex.on_training_start_action';

    /**
     * The milex.on_assist_remote_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Milex\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    const ON_ASSIST_REMOTE_ACTION = 'milex.on_assist_remote_action';

    /**
     * The milex.on_citrix_token_generate event is dispatched before a token is decoded.
     *
     * The event listener receives a MilexPlugin\MilexCitrixBundle\Event\TokenGenerateEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_TOKEN_GENERATE = 'milex.on_citrix_token_generate';

    /**
     * The milex.on_citrix_event_update event is dispatched when an event has been updated externally.
     *
     * The event listener receives a MilexPlugin\MilexCitrixBundle\Event\CitrixEventUpdateEvent instance.
     *
     * @var string
     */
    const ON_CITRIX_EVENT_UPDATE = 'milex.on_citrix_event_update';
}
