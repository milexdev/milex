<?php

namespace Milex\PluginBundle;

/**
 * Class PluginEvents.
 *
 * Events available for PluginEvents
 */
final class PluginEvents
{
    /**
     * The milex.plugin_on_integration_config_save event is dispatched when an integration's configuration is saved.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_CONFIG_SAVE = 'milex.plugin_on_integration_config_save';

    /**
     * The milex.plugin_on_integration_keys_encrypt event is dispatched prior to encrypting keys to be stored into the database.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationKeyEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_KEYS_ENCRYPT = 'milex.plugin_on_integration_keys_encrypt';

    /**
     * The milex.plugin_on_integration_keys_decrypt event is dispatched after fetching and decrypting keys from the database.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationKeyEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_KEYS_DECRYPT = 'milex.plugin_on_integration_keys_decrypt';

    /**
     * The milex.plugin_on_integration_keys_merge event is dispatched after new keys are merged into existing ones.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationKeyEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_KEYS_MERGE = 'milex.plugin_on_integration_keys_merge';

    /**
     * The milex.plugin_on_integration_request event is dispatched before a request is made.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationRequestEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_REQUEST = 'milex.plugin_on_integration_request';

    /**
     * The milex.plugin_on_integration_response event is dispatched after a request is made.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationRequestEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_RESPONSE = 'milex.plugin_on_integration_response';

    /**
     * The milex.plugin_on_integration_auth_redirect event is dispatched when an authorization URL is generated and before the user is redirected to it.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationAuthRedirectEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_AUTH_REDIRECT = 'milex.plugin_on_integration_auth_redirect';

    /**
     * The milex.plugin.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'milex.plugin.on_campaign_trigger_action';

    /**
     * The milex.plugin_on_integration_get_auth_callback_url event is dispatched when generating the redirect/callback URL.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationAuthCallbackUrlEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_GET_AUTH_CALLBACK_URL = 'milex.plugin_on_integration_get_auth_callback_url';

    /**
     * The milex.plugin_on_integration_form_display event is dispatched when fetching display settings for the integration's config form.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationFormDisplayEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_FORM_DISPLAY = 'milex.plugin_on_integration_form_display';

    /**
     * The milex.plugin_on_integration_form_build event is dispatched when building an integration's config form.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationFormBuildEvent instance.
     *
     * @var string
     */
    const PLUGIN_ON_INTEGRATION_FORM_BUILD = 'milex.plugin_on_integration_form_build';

    /**
     * The milex.plugin.on_form_submit_action_triggered event is dispatched when a plugin related submit action is executed.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginIntegrationFormBuildEvent instance.
     *
     * @var string
     */
    const ON_FORM_SUBMIT_ACTION_TRIGGERED = 'milex.plugin.on_form_submit_action_triggered';

    /**
     * The milex.plugin.on_plugin_update event is dispatched when a plugin is updated.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginUpdateEvent instance.
     *
     * @var string
     */
    const ON_PLUGIN_UPDATE = 'milex.plugin.on_plugin_update';

    /**
     * The milex.plugin.on_plugin_install event is dispatched when a plugin is installed.
     *
     * The event listener receives a Milex\PluginBundle\Event\PluginInstallEvent instance.
     *
     * @var string
     */
    const ON_PLUGIN_INSTALL = 'milex.plugin.on_plugin_install';
}
