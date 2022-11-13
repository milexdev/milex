<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle;

final class IntegrationEvents
{
    /**
     * The milex.integration.sync_post_execute_integration event is dispatched after a sync is executed.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\SyncEvent object.
     *
     * @var string
     */
    public const INTEGRATION_POST_EXECUTE = 'milex.integration.sync_post_execute_integration';

    /**
     * The milex.integration.config_form_loaded event is dispatched when config page for integration is loaded.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\FormLoadEvent object.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_FORM_LOAD = 'milex.integration.config_form_loaded';

    /**
     * The milex.integration.config_before_save event is dispatched prior to an integration's configuration is saved.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\ConfigSaveEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_BEFORE_SAVE = 'milex.integration.config_before_save';

    /**
     * The milex.integration.config_after_save event is dispatched after an integration's configuration is saved.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\ConfigSaveEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_AFTER_SAVE = 'milex.integration.config_after_save';

    /**
     * The milex.integration.keys_before_encryption event is dispatched prior to encrypting keys to be stored into the database.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\KeysEncryptionEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_KEYS_BEFORE_ENCRYPTION = 'milex.integration.keys_before_encryption';

    /**
     * The milex.integration.keys_after_decryption event is dispatched after fetching and decrypting keys from the database.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\KeysDecryptionEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_KEYS_AFTER_DECRYPTION = 'milex.integration.keys_after_decryption';

    /**
     * The milex.integration.milex_sync_field_load event is dispatched when Milex sync fields are build.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\MilexSyncFieldsLoadEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_MILEX_SYNC_FIELDS_LOAD = 'milex.integration.milex_sync_field_load';

    /**
     * The milex.integration.INTEGRATION_COLLECT_INTERNAL_OBJECTS event is dispatched when a list of Milex internal objects is build.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_COLLECT_INTERNAL_OBJECTS = 'milex.integration.INTEGRATION_COLLECT_INTERNAL_OBJECTS';

    /**
     * The milex.integration.INTEGRATION_CREATE_INTERNAL_OBJECTS event is dispatched when a list of Milex internal objects should be created.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectCreateEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CREATE_INTERNAL_OBJECTS = 'milex.integration.INTEGRATION_CREATE_INTERNAL_OBJECTS';

    /**
     * The milex.integration.INTEGRATION_UPDATE_INTERNAL_OBJECTS event is dispatched when a list of Milex internal objects should be updated.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectUpdateEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_UPDATE_INTERNAL_OBJECTS = 'milex.integration.INTEGRATION_UPDATE_INTERNAL_OBJECTS';

    /**
     * The milex.integration.INTEGRATION_FIND_INTERNAL_RECORDS event is dispatched when a list of Milex internal object records by ID is requested.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectFindEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_FIND_INTERNAL_RECORDS = 'milex.integration.INTEGRATION_FIND_INTERNAL_RECORDS';

    /**
     * The milex.integration.INTEGRATION_FIND_OWNER_IDS event is dispatched when a list of Milex internal owner IDs by internal object ID is requested.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectFindEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_FIND_OWNER_IDS = 'milex.integration.INTEGRATION_FIND_OWNER_IDS';

    /**
     * The milex.integration.INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE event is dispatched when a Milex internal object route is requested.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\InternalObjectOwnerEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE = 'milex.integration.INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE';

    /**
     * This event is dispatched when a tokens are being built to represent links to mapped integration objects.
     *
     * The event listener receives a Milex\IntegrationsBundle\Event\MappedIntegrationObjectTokenEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_OBJECT_TOKEN_EVENT = 'milex.integration.INTEGRATION_OBJECT_TOKEN_EVENT';
}
