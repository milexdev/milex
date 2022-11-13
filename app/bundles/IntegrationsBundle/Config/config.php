<?php

declare(strict_types=1);

return [
    'name'        => 'Integrations',
    'description' => 'Adds support for plugin integrations',
    'author'      => 'Milex, Inc.',
    'routes'      => [
        'main' => [
            'milex_integration_config' => [
                'path'       => '/integration/{integration}/config',
                'controller' => 'IntegrationsBundle:Config:edit',
            ],
            'milex_integration_config_field_pagination' => [
                'path'       => '/integration/{integration}/config/{object}/{page}',
                'controller' => 'IntegrationsBundle:FieldPagination:paginate',
                'defaults'   => [
                    'page' => 1,
                ],
            ],
            'milex_integration_config_field_update' => [
                'path'       => '/integration/{integration}/config/{object}/field/{field}',
                'controller' => 'IntegrationsBundle:UpdateField:update',
            ],
        ],
        'public' => [
            'milex_integration_public_callback' => [
                'path'       => '/integration/{integration}/callback',
                'controller' => 'IntegrationsBundle:Auth:callback',
            ],
        ],
    ],
    'services' => [
        'commands' => [
            'milex.integrations.command.sync' => [
                'class'     => \Milex\IntegrationsBundle\Command\SyncCommand::class,
                'arguments' => [
                    'milex.integrations.sync.service',
                    'milex.helper.core_parameters',
                ],
                'tag' => 'console.command',
            ],
        ],
        'events' => [
            'milex.integrations.subscriber.lead' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.integrations.repository.field_change',
                    'milex.integrations.repository.object_mapping',
                    'milex.integrations.helper.variable_expresser',
                    'milex.integrations.helper.sync_integrations',
                ],
            ],
            'milex.integrations.subscriber.contact_object' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\ContactObjectSubscriber::class,
                'arguments' => [
                    'milex.integrations.helper.contact_object',
                    'router',
                ],
            ],
            'milex.integrations.subscriber.company_object' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\CompanyObjectSubscriber::class,
                'arguments' => [
                    'milex.integrations.helper.company_object',
                    'router',
                ],
            ],
            'milex.integrations.subscriber.controller' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\ControllerSubscriber::class,
                'arguments' => [
                    'milex.integrations.helper',
                    'controller_resolver',
                ],
            ],
            'milex.integrations.subscriber.ui_contact_integrations_tab' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\UIContactIntegrationsTabSubscriber::class,
                'arguments' => [
                    'milex.integrations.repository.object_mapping',
                ],
            ],
            'milex.integrations.subscriber.contact_timeline_events' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\TimelineSubscriber::class,
                'arguments' => [
                    'milex.lead.repository.lead_event_log',
                    'translator',
                ],
            ],
            'milex.integrations.subscriber.email_subscriber' => [
                'class'     => \Milex\IntegrationsBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'translator',
                    'event_dispatcher',
                    'milex.integrations.token.parser',
                    'milex.integrations.repository.object_mapping',
                    'milex.helper.integration',
                ],
            ],
        ],
        'forms' => [
            'milex.integrations.form.config.integration' => [
                'class'     => \Milex\IntegrationsBundle\Form\Type\IntegrationConfigType::class,
                'arguments' => [
                    'milex.integrations.helper.config_integrations',
                ],
            ],
            'milex.integrations.form.config.feature_settings' => [
                'class' => \Milex\IntegrationsBundle\Form\Type\IntegrationFeatureSettingsType::class,
            ],
            'milex.integrations.form.config.sync_settings' => [
                'class' => \Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsType::class,
            ],
            'milex.integrations.form.config.sync_settings_field_mappings' => [
                'class'     => \Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsFieldMappingsType::class,
                'arguments' => [
                    'monolog.logger.milex',
                    'translator',
                ],
            ],
            'milex.integrations.form.config.sync_settings_object_field_directions' => [
                'class' => \Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsObjectFieldType::class,
            ],
            'milex.integrations.form.config.sync_settings_object_field_mapping' => [
                'class'     => \Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsObjectFieldMappingType::class,
                'arguments' => [
                    'translator',
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                ],
            ],
            'milex.integrations.form.config.sync_settings_object_field' => [
                'class' => \Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsObjectFieldType::class,
            ],
            'milex.integrations.form.config.feature_settings.activity_list' => [
                'class'     => \Milex\IntegrationsBundle\Form\Type\ActivityListType::class,
                'arguments' => [
                    'milex.lead.model.lead',
                ],
            ],
        ],
        'helpers' => [
            'milex.integrations.helper.variable_expresser' => [
                'class' => \Milex\IntegrationsBundle\Sync\VariableExpresser\VariableExpresserHelper::class,
            ],
            'milex.integrations.helper' => [
                'class'     => \Milex\IntegrationsBundle\Helper\IntegrationsHelper::class,
                'arguments' => [
                    'milex.plugin.integrations.repository.integration',
                    'milex.integrations.service.encryption',
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.helper.auth_integrations' => [
                'class'     => \Milex\IntegrationsBundle\Helper\AuthIntegrationsHelper::class,
                'arguments' => [
                    'milex.integrations.helper',
                ],
            ],
            'milex.integrations.helper.sync_integrations' => [
                'class'     => \Milex\IntegrationsBundle\Helper\SyncIntegrationsHelper::class,
                'arguments' => [
                    'milex.integrations.helper',
                    'milex.integrations.internal.object_provider',
                ],
            ],
            'milex.integrations.helper.config_integrations' => [
                'class'     => \Milex\IntegrationsBundle\Helper\ConfigIntegrationsHelper::class,
                'arguments' => [
                    'milex.integrations.helper',
                ],
            ],
            'milex.integrations.helper.builder_integrations' => [
                'class'     => \Milex\IntegrationsBundle\Helper\BuilderIntegrationsHelper::class,
                'arguments' => [
                    'milex.integrations.helper',
                ],
            ],
            'milex.integrations.helper.field_validator' => [
                'class'     => \Milex\IntegrationsBundle\Helper\FieldValidationHelper::class,
                'arguments' => [
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                    'translator',
                ],
            ],
        ],
        'other' => [
            'milex.integrations.service.encryption' => [
                'class'     => \Milex\IntegrationsBundle\Facade\EncryptionService::class,
                'arguments' => [
                    'milex.helper.encryption',
                ],
            ],
            'milex.integrations.internal.object_provider' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.sync.notification.helper.owner_provider' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\OwnerProvider::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.integrations.internal.object_provider',
                ],
            ],
            'milex.integrations.auth_provider.api_key' => [
                'class' => \Milex\IntegrationsBundle\Auth\Provider\ApiKey\HttpFactory::class,
            ],
            'milex.integrations.auth_provider.basic_auth' => [
                'class' => \Milex\IntegrationsBundle\Auth\Provider\BasicAuth\HttpFactory::class,
            ],
            'milex.integrations.auth_provider.oauth1atwolegged' => [
                'class' => \Milex\IntegrationsBundle\Auth\Provider\Oauth1aTwoLegged\HttpFactory::class,
            ],
            'milex.integrations.auth_provider.oauth2twolegged' => [
                'class' => \Milex\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\HttpFactory::class,
            ],
            'milex.integrations.auth_provider.oauth2threelegged' => [
                'class' => \Milex\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\HttpFactory::class,
            ],
            'milex.integrations.auth_provider.token_persistence_factory' => [
                'class'     => \Milex\IntegrationsBundle\Auth\Support\Oauth2\Token\TokenPersistenceFactory::class,
                'arguments' => ['milex.integrations.helper'],
            ],
            'milex.integrations.token.parser' => [
                'class' => \Milex\IntegrationsBundle\Helper\TokenParser::class,
            ],
        ],
        'repositories' => [
            'milex.integrations.repository.field_change' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\IntegrationsBundle\Entity\FieldChange::class,
                ],
            ],
            'milex.integrations.repository.object_mapping' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\IntegrationsBundle\Entity\ObjectMapping::class,
                ],
            ],
            // Placeholder till the plugin bundle implements this
            'milex.plugin.integrations.repository.integration' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PluginBundle\Entity\Integration::class,
                ],
            ],
        ],
        'sync' => [
            'milex.sync.logger' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Logger\DebugLogger::class,
                'arguments' => [
                    'monolog.logger.milex',
                ],
            ],
            'milex.integrations.helper.sync_judge' => [
                'class' => \Milex\IntegrationsBundle\Sync\SyncJudge\SyncJudge::class,
            ],
            'milex.integrations.helper.contact_object' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\ContactObjectHelper::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.repository.lead',
                    'doctrine.dbal.default_connection',
                    'milex.lead.model.field',
                    'milex.lead.model.dnc',
                    'milex.lead.model.company',
                ],
            ],
            'milex.integrations.helper.company_object' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\CompanyObjectHelper::class,
                'arguments' => [
                    'milex.lead.model.company',
                    'milex.lead.repository.company',
                    'doctrine.dbal.default_connection',
                ],
            ],
            'milex.integrations.sync.data_exchange.milex.order_executioner' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner::class,
                'arguments' => [
                    'milex.integrations.helper.sync_mapping',
                    'event_dispatcher',
                    'milex.integrations.internal.object_provider',
                ],
            ],
            'milex.integrations.sync.data_exchange.milex.field_helper' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.integrations.helper.variable_expresser',
                    'milex.channel.helper.channel_list',
                    'translator',
                    'event_dispatcher',
                    'milex.integrations.internal.object_provider',
                ],
            ],
            'milex.integrations.sync.sync_process.value_helper' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper::class,
                'arguments' => [],
            ],
            'milex.integrations.sync.data_exchange.milex.field_builder' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FieldBuilder::class,
                'arguments' => [
                    'router',
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                    'milex.integrations.helper.contact_object',
                ],
            ],
            'milex.integrations.sync.data_exchange.milex.full_object_report_builder' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder::class,
                'arguments' => [
                    'milex.integrations.sync.data_exchange.milex.field_builder',
                    'milex.integrations.internal.object_provider',
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.sync.data_exchange.milex.partial_object_report_builder' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder::class,
                'arguments' => [
                    'milex.integrations.repository.field_change',
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                    'milex.integrations.sync.data_exchange.milex.field_builder',
                    'milex.integrations.internal.object_provider',
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.sync.data_exchange.milex' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange::class,
                'arguments' => [
                    'milex.integrations.repository.field_change',
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                    'milex.integrations.helper.sync_mapping',
                    'milex.integrations.sync.data_exchange.milex.full_object_report_builder',
                    'milex.integrations.sync.data_exchange.milex.partial_object_report_builder',
                    'milex.integrations.sync.data_exchange.milex.order_executioner',
                ],
            ],
            'milex.integrations.sync.integration_process.object_change_generator' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\ObjectChangeGenerator::class,
                'arguments' => [
                    'milex.integrations.sync.sync_process.value_helper',
                ],
            ],
            'milex.integrations.sync.integration_process' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess::class,
                'arguments' => [
                    'milex.integrations.helper.sync_date',
                    'milex.integrations.helper.sync_mapping',
                    'milex.integrations.sync.integration_process.object_change_generator',
                ],
            ],
            'milex.integrations.sync.internal_process.object_change_generator' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\ObjectChangeGenerator::class,
                'arguments' => [
                    'milex.integrations.helper.sync_judge',
                    'milex.integrations.sync.sync_process.value_helper',
                    'milex.integrations.sync.data_exchange.milex.field_helper',
                ],
            ],
            'milex.integrations.sync.internal_process' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MilexSyncProcess::class,
                'arguments' => [
                    'milex.integrations.helper.sync_date',
                    'milex.integrations.sync.internal_process.object_change_generator',
                ],
            ],
            'milex.integrations.sync.service' => [
                'class'     => \Milex\IntegrationsBundle\Sync\SyncService\SyncService::class,
                'arguments' => [
                    'milex.integrations.sync.data_exchange.milex',
                    'milex.integrations.helper.sync_date',
                    'milex.integrations.helper.sync_mapping',
                    'milex.integrations.sync.helper.relations',
                    'milex.integrations.helper.sync_integrations',
                    'event_dispatcher',
                    'milex.integrations.sync.notifier',
                    'milex.integrations.sync.integration_process',
                    'milex.integrations.sync.internal_process',
                ],
                'methodCalls' => [
                    'initiateDebugLogger' => ['milex.sync.logger'],
                ],
            ],
            'milex.integrations.helper.sync_date' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Helper\SyncDateHelper::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                ],
            ],
            'milex.integrations.helper.sync_mapping' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Helper\MappingHelper::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.integrations.repository.object_mapping',
                    'milex.integrations.internal.object_provider',
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.sync.helper.relations' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Helper\RelationsHelper::class,
                'arguments' => [
                    'milex.integrations.helper.sync_mapping',
                ],
            ],
            'milex.integrations.sync.notifier' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Notifier::class,
                'arguments' => [
                    'milex.integrations.sync.notification.handler_container',
                    'milex.integrations.helper.sync_integrations',
                    'milex.integrations.helper.config_integrations',
                ],
            ],
            'milex.integrations.sync.notification.writer' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Writer::class,
                'arguments' => [
                    'milex.core.model.notification',
                    'milex.core.model.auditlog',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.integrations.sync.notification.handler_container' => [
                'class' => \Milex\IntegrationsBundle\Sync\Notification\Handler\HandlerContainer::class,
            ],
            'milex.integrations.sync.notification.handler_company' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Handler\CompanyNotificationHandler::class,
                'arguments' => [
                    'milex.integrations.sync.notification.writer',
                    'milex.integrations.sync.notification.helper_user_notification',
                    'milex.integrations.sync.notification.helper_company',
                ],
                'tag' => 'milex.sync.notification_handler',
            ],
            'milex.integrations.sync.notification.handler_contact' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Handler\ContactNotificationHandler::class,
                'arguments' => [
                    'milex.integrations.sync.notification.writer',
                    'milex.lead.repository.lead_event_log',
                    'doctrine.orm.entity_manager',
                    'milex.integrations.sync.notification.helper_user_summary_notification',
                ],
                'tag' => 'milex.sync.notification_handler',
            ],
            'milex.integrations.sync.notification.helper_company' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\CompanyHelper::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                ],
            ],
            'milex.integrations.sync.notification.helper_user' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\UserHelper::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                ],
            ],
            'milex.integrations.sync.notification.helper_route' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\RouteHelper::class,
                'arguments' => [
                    'milex.integrations.internal.object_provider',
                    'event_dispatcher',
                ],
            ],
            'milex.integrations.sync.notification.helper_user_notification' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\UserNotificationHelper::class,
                'arguments' => [
                    'milex.integrations.sync.notification.writer',
                    'milex.integrations.sync.notification.helper_user',
                    'milex.integrations.sync.notification.helper.owner_provider',
                    'milex.integrations.sync.notification.helper_route',
                    'translator',
                ],
            ],
            'milex.integrations.sync.notification.helper_user_summary_notification' => [
                'class'     => \Milex\IntegrationsBundle\Sync\Notification\Helper\UserSummaryNotificationHelper::class,
                'arguments' => [
                    'milex.integrations.sync.notification.writer',
                    'milex.integrations.sync.notification.helper_user',
                    'milex.integrations.sync.notification.helper.owner_provider',
                    'milex.integrations.sync.notification.helper_route',
                    'translator',
                ],
            ],
        ],
    ],
];
