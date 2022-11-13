<?php

return [
    'routes' => [
        'main' => [
            'milex_core_ajax' => [
                'path'       => '/ajax',
                'controller' => 'MilexCoreBundle:Ajax:delegateAjax',
            ],
            'milex_core_update' => [
                'path'       => '/update',
                'controller' => 'MilexCoreBundle:Update:index',
            ],
            'milex_core_update_schema' => [
                'path'       => '/update/schema',
                'controller' => 'MilexCoreBundle:Update:schema',
            ],
            'milex_core_form_action' => [
                'path'       => '/action/{objectAction}/{objectModel}/{objectId}',
                'controller' => 'MilexCoreBundle:Form:execute',
                'defaults'   => [
                    'objectModel' => '',
                ],
            ],
            'milex_core_file_action' => [
                'path'       => '/file/{objectAction}/{objectId}',
                'controller' => 'MilexCoreBundle:File:execute',
            ],
            'milex_themes_index' => [
                'path'       => '/themes',
                'controller' => 'MilexCoreBundle:Theme:index',
            ],
            'milex_themes_action' => [
                'path'       => '/themes/{objectAction}/{objectId}',
                'controller' => 'MilexCoreBundle:Theme:execute',
            ],
        ],
        'public' => [
            'milex_js' => [
                'path'       => '/mtc.js',
                'controller' => 'MilexCoreBundle:Js:index',
            ],
            'milex_base_index' => [
                'path'       => '/',
                'controller' => 'MilexCoreBundle:Default:index',
            ],
            'milex_secure_root' => [
                'path'       => '/s',
                'controller' => 'MilexCoreBundle:Default:redirectSecureRoot',
            ],
            'milex_secure_root_slash' => [
                'path'       => '/s/',
                'controller' => 'MilexCoreBundle:Default:redirectSecureRoot',
            ],
            'milex_remove_trailing_slash' => [
                'path'         => '/{url}',
                'controller'   => 'MilexCoreBundle:Common:removeTrailingSlash',
                'method'       => 'GET',
                'requirements' => [
                    'url' => '.*/$',
                ],
            ],
        ],
        'api' => [
            'milex_core_api_file_list' => [
                'path'       => '/files/{dir}',
                'controller' => 'MilexCoreBundle:Api\FileApi:list',
            ],
            'milex_core_api_file_create' => [
                'path'       => '/files/{dir}/new',
                'controller' => 'MilexCoreBundle:Api\FileApi:create',
                'method'     => 'POST',
            ],
            'milex_core_api_file_delete' => [
                'path'       => '/files/{dir}/{file}/delete',
                'controller' => 'MilexCoreBundle:Api\FileApi:delete',
                'method'     => 'DELETE',
            ],
            'milex_core_api_theme_list' => [
                'path'       => '/themes',
                'controller' => 'MilexCoreBundle:Api\ThemeApi:list',
            ],
            'milex_core_api_theme_get' => [
                'path'       => '/themes/{theme}',
                'controller' => 'MilexCoreBundle:Api\ThemeApi:get',
            ],
            'milex_core_api_theme_create' => [
                'path'       => '/themes/new',
                'controller' => 'MilexCoreBundle:Api\ThemeApi:new',
                'method'     => 'POST',
            ],
            'milex_core_api_theme_delete' => [
                'path'       => '/themes/{theme}/delete',
                'controller' => 'MilexCoreBundle:Api\ThemeApi:delete',
                'method'     => 'DELETE',
            ],
            'milex_core_api_stats' => [
                'path'       => '/stats/{table}',
                'controller' => 'MilexCoreBundle:Api\StatsApi:list',
                'defaults'   => [
                    'table' => '',
                ],
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'milex.core.components' => [
                'id'        => 'milex_components_root',
                'iconClass' => 'fa-puzzle-piece',
                'priority'  => 60,
            ],
            'milex.core.channels' => [
                'id'        => 'milex_channels_root',
                'iconClass' => 'fa-rss',
                'priority'  => 40,
            ],
        ],
        'admin' => [
            'milex.theme.menu.index' => [
                'route'     => 'milex_themes_index',
                'iconClass' => 'fa-newspaper-o',
                'id'        => 'milex_themes_index',
                'access'    => 'core:themes:view',
            ],
        ],
        'extra' => [
            'priority' => -1000,
            'items'    => [
                'name'     => 'extra',
                'children' => [],
            ],
        ],
        'profile' => [
            'priority' => -1000,
            'items'    => [
                'name'     => 'profile',
                'children' => [],
            ],
        ],
    ],
    'services' => [
        'main' => [
            'milex.core.service.flashbag' => [
                'class'     => \Milex\CoreBundle\Service\FlashBag::class,
                'arguments' => [
                    '@session',
                    'translator',
                    'request_stack',
                    'milex.core.model.notification',
                ],
            ],
            'milex.core.service.local_file_adapter' => [
                'class'     => \Milex\CoreBundle\Service\LocalFileAdapterService::class,
                'arguments' => [
                    '%env(resolve:MILEX_EL_FINDER_PATH)%',
                ],
            ],
            'milex.core.service.log_processor' => [
                'class'     => \Milex\CoreBundle\Monolog\LogProcessor::class,
                'tags'      => ['monolog.processor'],
            ],
        ],
        'events' => [
            'milex.core.subscriber' => [
                'class'     => Milex\CoreBundle\EventListener\CoreSubscriber::class,
                'arguments' => [
                    'milex.helper.bundle',
                    'milex.helper.menu',
                    'milex.helper.user',
                    'templating.helper.assets',
                    'milex.helper.core_parameters',
                    'security.authorization_checker',
                    'milex.user.model.user',
                    'event_dispatcher',
                    'translator',
                    'request_stack',
                    'milex.form.repository.form',
                    'milex.factory',
                    'milex.core.service.flashbag',
                ],
            ],
            'milex.core.environment.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\EnvironmentSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.core.migration.command.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\MigrationCommandSubscriber::class,
                'arguments' => [
                    'milex.database.version.provider',
                    'milex.generated.columns.provider',
                    'database_connection',
                ],
            ],
            'milex.core.configbundle.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\ConfigSubscriber::class,
                'arguments' => [
                    'milex.helper.language',
                ],
            ],
            'milex.core.configbundle.subscriber.theme' => [
                'class'     => \Milex\CoreBundle\EventListener\ConfigThemeSubscriber::class,
            ],
            'milex.webpush.js.subscriber' => [
                'class' => \Milex\CoreBundle\EventListener\BuildJsSubscriber::class,
            ],
            'milex.core.dashboard.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.core.model.auditlog',
                    'translator',
                    'router',
                    'milex.security',
                    'event_dispatcher',
                    'milex.model.factory',
                ],
            ],

            'milex.core.maintenance.subscriber' => [
                'class'     => Milex\CoreBundle\EventListener\MaintenanceSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'milex.user.token.repository',
                    'translator',
                ],
            ],
            'milex.core.request.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\RequestSubscriber::class,
                'arguments' => [
                    'security.csrf.token_manager',
                    'translator',
                    'milex.helper.templating',
                ],
            ],
            'milex.core.stats.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.core.assets.subscriber' => [
                'class'     => \Milex\CoreBundle\EventListener\AssetsSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'event_dispatcher',
                ],
            ],
            'milex.core.subscriber.router' => [
                'class'     => \Milex\CoreBundle\EventListener\RouterSubscriber::class,
                'arguments' => [
                    'router',
                    '%router.request_context.scheme%',
                    '%router.request_context.host%',
                    '%request_listener.https_port%',
                    '%request_listener.http_port%',
                    '%router.request_context.base_url%',
                ],
            ],
            'milex.core.subscriber.editor_assets' => [
                'class'       => \Milex\CoreBundle\EventListener\EditorFontsSubscriber::class,
                'arguments'   => [
                    'milex.helper.core_parameters',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.button_group' => [
                'class' => 'Milex\CoreBundle\Form\Type\ButtonGroupType',
            ],
            'milex.form.type.standalone_button' => [
                'class' => 'Milex\CoreBundle\Form\Type\StandAloneButtonType',
            ],
            'milex.form.type.form_buttons' => [
                'class' => 'Milex\CoreBundle\Form\Type\FormButtonsType',
            ],
            'milex.form.type.sortablelist' => [
                'class' => 'Milex\CoreBundle\Form\Type\SortableListType',
            ],
            'milex.form.type.coreconfig' => [
                'class'     => \Milex\CoreBundle\Form\Type\ConfigType::class,
                'arguments' => [
                    'translator',
                    'milex.helper.language',
                    'milex.ip_lookup.factory',
                    '%milex.ip_lookup_services%',
                    'milex.ip_lookup',
                ],
            ],
            'milex.form.type.coreconfig.iplookup_download_data_store_button' => [
                'class'     => \Milex\CoreBundle\Form\Type\IpLookupDownloadDataStoreButtonType::class,
                'arguments' => [
                    'milex.helper.template.date',
                    'translator',
                ],
            ],
            'milex.form.type.theme_list' => [
                'class'     => \Milex\CoreBundle\Form\Type\ThemeListType::class,
                'arguments' => ['milex.helper.theme'],
            ],
            'milex.form.type.daterange' => [
                'class'     => \Milex\CoreBundle\Form\Type\DateRangeType::class,
                'arguments' => [
                    'session',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.form.type.timeformat' => [
                'class'     => \Milex\CoreBundle\Form\Type\TimeFormatType::class,
                'arguments' => ['translator'],
            ],
            'milex.form.type.slot.saveprefsbutton' => [
                'class'     => 'Milex\CoreBundle\Form\Type\SlotSavePrefsButtonType',
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.slot.successmessage' => [
                'class'     => Milex\CoreBundle\Form\Type\SlotSuccessMessageType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.slot.gatedvideo' => [
                'class'     => Milex\CoreBundle\Form\Type\GatedVideoType::class,
                'arguments' => [
                    'milex.form.repository.form',
                ],
            ],
            'milex.form.type.slot.segmentlist' => [
                'class'     => 'Milex\CoreBundle\Form\Type\SlotSegmentListType',
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.slot.categorylist' => [
                'class'     => \Milex\CoreBundle\Form\Type\SlotCategoryListType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.slot.preferredchannel' => [
                'class'     => \Milex\CoreBundle\Form\Type\SlotPreferredChannelType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.slot.channelfrequency' => [
                'class'     => \Milex\CoreBundle\Form\Type\SlotChannelFrequencyType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.dynamic_content_filter_entry' => [
                'class'     => \Milex\CoreBundle\Form\Type\DynamicContentFilterEntryType::class,
                'arguments' => [
                    'milex.lead.model.list',
                    'milex.stage.model.stage',
                    'milex.integrations.helper.builder_integrations',
                ],
            ],
            'milex.form.type.dynamic_content_filter_entry_filters' => [
                'class'     => \Milex\CoreBundle\Form\Type\DynamicContentFilterEntryFiltersType::class,
                'arguments' => [
                    'translator',
                ],
                'methodCalls' => [
                    'setConnection' => [
                        'database_connection',
                    ],
                ],
            ],
            'milex.form.type.entity_lookup' => [
                'class'     => \Milex\CoreBundle\Form\Type\EntityLookupType::class,
                'arguments' => [
                    'milex.model.factory',
                    'translator',
                    'database_connection',
                    'router',
                ],
            ],
            'milex.form.type.dynamic_content_filter' => [
                'class'     => \Milex\CoreBundle\Form\Type\DynamicContentFilterType::class,
                'arguments' => [
                    'milex.integrations.helper.builder_integrations',
                ],
            ],
        ],
        'helpers' => [
            'milex.helper.app_version' => [
                'class' => \Milex\CoreBundle\Helper\AppVersion::class,
            ],
            'milex.helper.template.menu' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\MenuHelper::class,
                'arguments' => ['knp_menu.helper'],
                'alias'     => 'menu',
            ],
            'milex.helper.template.date' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\DateHelper::class,
                'arguments' => [
                    '%milex.date_format_full%',
                    '%milex.date_format_short%',
                    '%milex.date_format_dateonly%',
                    '%milex.date_format_timeonly%',
                    'translator',
                    'milex.helper.core_parameters',
                ],
                'alias' => 'date',
            ],
            'milex.helper.template.exception' => [
                'class'     => 'Milex\CoreBundle\Templating\Helper\ExceptionHelper',
                'arguments' => '%kernel.root_dir%',
                'alias'     => 'exception',
            ],
            'milex.helper.template.gravatar' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\GravatarHelper::class,
                'arguments' => [
                    'milex.helper.template.default_avatar',
                    'milex.helper.core_parameters',
                    'request_stack',
                ],
                'alias'     => 'gravatar',
            ],
            'milex.helper.template.analytics' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\AnalyticsHelper::class,
                'alias'     => 'analytics',
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.helper.template.config' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\ConfigHelper::class,
                'alias'     => 'config',
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.helper.template.mautibot' => [
                'class' => 'Milex\CoreBundle\Templating\Helper\MautibotHelper',
                'alias' => 'mautibot',
            ],
            'milex.helper.template.canvas' => [
                'class'     => 'Milex\CoreBundle\Templating\Helper\SidebarCanvasHelper',
                'arguments' => [
                    'event_dispatcher',
                ],
                'alias' => 'canvas',
            ],
            'milex.helper.template.button' => [
                'class'     => 'Milex\CoreBundle\Templating\Helper\ButtonHelper',
                'arguments' => [
                    'templating',
                    'translator',
                    'event_dispatcher',
                ],
                'alias' => 'buttons',
            ],
            'milex.helper.template.content' => [
                'class'     => 'Milex\CoreBundle\Templating\Helper\ContentHelper',
                'arguments' => [
                    'templating',
                    'event_dispatcher',
                ],
                'alias' => 'content',
            ],
            'milex.helper.template.formatter' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\FormatterHelper::class,
                'arguments' => [
                    'milex.helper.template.date',
                    'translator',
                ],
                'alias' => 'formatter',
            ],
            'milex.helper.template.version' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\VersionHelper::class,
                'arguments' => [
                    'milex.helper.app_version',
                ],
                'alias' => 'version',
            ],
            'milex.helper.template.security' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\SecurityHelper::class,
                'arguments' => [
                    'milex.security',
                    'request_stack',
                    'event_dispatcher',
                    'security.csrf.token_manager',
                ],
                'alias' => 'security',
            ],
            'milex.helper.template.translator' => [
                'class'     => \Milex\CoreBundle\Templating\Helper\TranslatorHelper::class,
                'arguments' => [
                    'translator',
                ],
                'alias' => 'translator',
            ],
            'milex.helper.paths' => [
                'class'     => 'Milex\CoreBundle\Helper\PathsHelper',
                'arguments' => [
                    'milex.helper.user',
                    'milex.helper.core_parameters',
                    '%kernel.cache_dir%',
                    '%kernel.logs_dir%',
                    '%kernel.root_dir%',
                ],
            ],
            'milex.helper.ip_lookup' => [
                'class'     => 'Milex\CoreBundle\Helper\IpLookupHelper',
                'arguments' => [
                    'request_stack',
                    'doctrine.orm.entity_manager',
                    'milex.helper.core_parameters',
                    'milex.ip_lookup',
                ],
            ],
            'milex.helper.user' => [
                'class'     => 'Milex\CoreBundle\Helper\UserHelper',
                'arguments' => [
                    'security.token_storage',
                ],
            ],
            'milex.helper.core_parameters' => [
                'class'     => \Milex\CoreBundle\Helper\CoreParametersHelper::class,
                'arguments' => [
                    'service_container',
                ],
                'serviceAlias' => 'milex.config',
            ],
            'milex.helper.bundle' => [
                'class'     => 'Milex\CoreBundle\Helper\BundleHelper',
                'arguments' => [
                    '%milex.bundles%',
                    '%milex.plugin.bundles%',
                ],
            ],
            'milex.helper.phone_number' => [
                'class' => 'Milex\CoreBundle\Helper\PhoneNumberHelper',
            ],
            'milex.helper.input_helper' => [
                'class' => \Milex\CoreBundle\Helper\InputHelper::class,
            ],
            'milex.helper.file_uploader' => [
                'class'     => \Milex\CoreBundle\Helper\FileUploader::class,
                'arguments' => [
                    'milex.helper.file_path_resolver',
                ],
            ],
            'milex.helper.file_path_resolver' => [
                'class'     => \Milex\CoreBundle\Helper\FilePathResolver::class,
                'arguments' => [
                    'symfony.filesystem',
                    'milex.helper.input_helper',
                ],
            ],
            'milex.helper.file_properties' => [
                'class' => \Milex\CoreBundle\Helper\FileProperties::class,
            ],
            'milex.helper.trailing_slash' => [
                'class'     => \Milex\CoreBundle\Helper\TrailingSlashHelper::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.helper.token_builder' => [
                'class'     => \Milex\CoreBundle\Helper\BuilderTokenHelper::class,
                'arguments' => [
                    'milex.security',
                    'milex.model.factory',
                    'database_connection',
                    'milex.helper.user',
                ],
            ],
            'milex.helper.token_builder.factory' => [
                'class'     => \Milex\CoreBundle\Helper\BuilderTokenHelperFactory::class,
                'arguments' => [
                    'milex.security',
                    'milex.model.factory',
                    'database_connection',
                    'milex.helper.user',
                ],
            ],
            'milex.helper.maxmind_do_not_sell_download' => [
                'class'     => \Milex\CoreBundle\Helper\MaxMindDoNotSellDownloadHelper::class,
                'arguments' => [
                    '%milex.ip_lookup_auth%',
                    'monolog.logger.milex',
                    'milex.native.connector',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.helper.update_checks' => [
                'class' => \Milex\CoreBundle\Helper\PreUpdateCheckHelper::class,
            ],
        ],
        'menus' => [
            'milex.menu.main' => [
                'alias' => 'main',
            ],
            'milex.menu.admin' => [
                'alias'   => 'admin',
                'options' => [
                    'template' => 'MilexCoreBundle:Menu:admin.html.php',
                ],
            ],
            'milex.menu.extra' => [
                'alias'   => 'extra',
                'options' => [
                    'template' => 'MilexCoreBundle:Menu:extra.html.php',
                ],
            ],
            'milex.menu.profile' => [
                'alias'   => 'profile',
                'options' => [
                    'template' => 'MilexCoreBundle:Menu:profile_inline.html.php',
                ],
            ],
        ],
        'commands' => [
            'milex.core.command.transifex_pull' => [
                'tag'       => 'console.command',
                'class'     => \Milex\CoreBundle\Command\PullTransifexCommand::class,
                'arguments' => [
                    'transifex.factory',
                    'translator',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.core.command.transifex_push' => [
                'tag'       => 'console.command',
                'class'     => \Milex\CoreBundle\Command\PushTransifexCommand::class,
                'arguments' => [
                    'transifex.factory',
                    'translator',
                ],
            ],
            'milex.core.command.do_not_sell' => [
                'class'     => \Milex\CoreBundle\Command\UpdateDoNotSellListCommand::class,
                'arguments' => [
                    'milex.helper.maxmind_do_not_sell_download',
                    'translator',
                ],
                'tag' => 'console.command',
            ],
            'milex.core.command.apply_update' => [
                'tag'       => 'console.command',
                'class'     => \Milex\CoreBundle\Command\ApplyUpdatesCommand::class,
                'arguments' => [
                    'translator',
                    'milex.helper.core_parameters',
                    'milex.update.step_provider',
                ],
            ],
            'milex.core.command.maxmind.purge' => [
                'tag'       => 'console.command',
                'class'     => \Milex\CoreBundle\Command\MaxMindDoNotSellPurgeCommand::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.maxmind.doNotSellList',
                ],
            ],
        ],
        'other' => [
            'milex.cache.warmer.middleware' => [
                'class'     => \Milex\CoreBundle\Cache\MiddlewareCacheWarmer::class,
                'tag'       => 'kernel.cache_warmer',
                'arguments' => [
                    '%kernel.environment%',
                ],
            ],
            'milex.http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
            /* @deprecated to be removed in Milex 4. Use 'milex.filesystem' instead. */
            'symfony.filesystem' => [
                'class' => \Symfony\Component\Filesystem\Filesystem::class,
            ],
            'milex.filesystem' => [
                'class' => \Milex\CoreBundle\Helper\Filesystem::class,
            ],
            'symfony.finder' => [
                'class' => \Symfony\Component\Finder\Finder::class,
            ],
            // Error handler
            'milex.core.errorhandler.subscriber' => [
                'class'     => 'Milex\CoreBundle\EventListener\ErrorHandlingListener',
                'arguments' => [
                    'monolog.logger.milex',
                    'monolog.logger',
                    "@=container.has('monolog.logger.chrome') ? container.get('monolog.logger.chrome') : null",
                ],
                'tag' => 'kernel.event_subscriber',
            ],

            // Configurator (used in installer and managing global config]
            'milex.configurator' => [
                'class'     => 'Milex\CoreBundle\Configurator\Configurator',
                'arguments' => [
                    'milex.helper.paths',
                ],
            ],

            // System uses
            'milex.di.env_processor.nullable' => [
                'class' => \Milex\CoreBundle\DependencyInjection\EnvProcessor\NullableProcessor::class,
                'tag'   => 'container.env_var_processor',
            ],
            'milex.di.env_processor.int_nullable' => [
                'class' => \Milex\CoreBundle\DependencyInjection\EnvProcessor\IntNullableProcessor::class,
                'tag'   => 'container.env_var_processor',
            ],
            'milex.di.env_processor.milexconst' => [
                'class' => \Milex\CoreBundle\DependencyInjection\EnvProcessor\MilexConstProcessor::class,
                'tag'   => 'container.env_var_processor',
            ],
            'milex.cipher.openssl' => [
                'class'     => \Milex\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher::class,
                'arguments' => ['%kernel.environment%'],
            ],
            'milex.factory' => [
                'class'     => 'Milex\CoreBundle\Factory\MilexFactory',
                'arguments' => 'service_container',
            ],
            'milex.model.factory' => [
                'class'     => 'Milex\CoreBundle\Factory\ModelFactory',
                'arguments' => 'service_container',
            ],
            'milex.templating.name_parser' => [
                'class'     => 'Milex\CoreBundle\Templating\TemplateNameParser',
                'arguments' => 'kernel',
            ],
            'milex.route_loader' => [
                'class'     => 'Milex\CoreBundle\Loader\RouteLoader',
                'arguments' => [
                    'event_dispatcher',
                    'milex.helper.core_parameters',
                ],
                'tag' => 'routing.loader',
            ],
            'milex.security' => [
                'class'     => 'Milex\CoreBundle\Security\Permissions\CorePermissions',
                'arguments' => [
                    'milex.helper.user',
                    'translator',
                    'milex.helper.core_parameters',
                    '%milex.bundles%',
                    '%milex.plugin.bundles%',
                ],
            ],
            'milex.page.helper.factory' => [
                'class'     => \Milex\CoreBundle\Factory\PageHelperFactory::class,
                'arguments' => [
                    'session',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.translation.loader' => [
                'class'     => \Milex\CoreBundle\Loader\TranslationLoader::class,
                'arguments' => [
                    'milex.helper.bundle',
                    'milex.helper.paths',
                ],
                'tag'       => 'translation.loader',
                'alias'     => 'milex',
            ],
            'milex.tblprefix_subscriber' => [
                'class'     => 'Milex\CoreBundle\EventListener\DoctrineEventsSubscriber',
                'tag'       => 'doctrine.event_subscriber',
                'arguments' => '%milex.db_table_prefix%',
            ],
            'milex.database.version.provider' => [
                'class'     => \Milex\CoreBundle\Doctrine\Provider\VersionProvider::class,
                'arguments' => ['database_connection', 'milex.helper.core_parameters'],
            ],
            'milex.generated.columns.provider' => [
                'class'     => \Milex\CoreBundle\Doctrine\Provider\GeneratedColumnsProvider::class,
                'arguments' => ['milex.database.version.provider', 'event_dispatcher'],
            ],
            'milex.generated.columns.doctrine.listener' => [
                'class'        => \Milex\CoreBundle\EventListener\DoctrineGeneratedColumnsListener::class,
                'tag'          => 'doctrine.event_listener',
                'tagArguments' => [
                    'event' => 'postGenerateSchema',
                    'lazy'  => true,
                ],
                'arguments' => [
                    'milex.generated.columns.provider',
                    'monolog.logger.milex',
                ],
            ],
            'milex.exception.listener' => [
                'class'     => 'Milex\CoreBundle\EventListener\ExceptionListener',
                'arguments' => [
                    'router',
                    '"MilexCoreBundle:Exception:show"',
                    'monolog.logger.milex',
                ],
                'tag'          => 'kernel.event_listener',
                'tagArguments' => [
                    'event'    => 'kernel.exception',
                    'method'   => 'onKernelException',
                    'priority' => 255,
                ],
            ],
            'transifex.factory' => [
                'class'     => \Milex\CoreBundle\Factory\TransifexFactory::class,
                'arguments' => [
                    'milex.http.client',
                    'milex.helper.core_parameters',
                ],
            ],
            // Helpers
            'milex.helper.assetgeneration' => [
                'class'     => \Milex\CoreBundle\Helper\AssetGenerationHelper::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.helper.bundle',
                    'milex.helper.paths',
                    'milex.helper.app_version',
                ],
            ],
            'milex.helper.cookie' => [
                'class'     => 'Milex\CoreBundle\Helper\CookieHelper',
                'arguments' => [
                    '%milex.cookie_path%',
                    '%milex.cookie_domain%',
                    '%milex.cookie_secure%',
                    '%milex.cookie_httponly%',
                    'request_stack',
                ],
            ],
            'milex.helper.cache_storage' => [
                'class'     => Milex\CoreBundle\Helper\CacheStorageHelper::class,
                'arguments' => [
                    '"db"',
                    '%milex.db_table_prefix%',
                    'doctrine.dbal.default_connection',
                    '%kernel.cache_dir%',
                ],
            ],
            'milex.helper.update' => [
                'class'     => \Milex\CoreBundle\Helper\UpdateHelper::class,
                'arguments' => [
                    'milex.helper.paths',
                    'monolog.logger.milex',
                    'milex.helper.core_parameters',
                    'milex.http.client',
                    'milex.helper.update.release_parser',
                    'milex.helper.update_checks',
                ],
            ],
            'milex.helper.update.release_parser' => [
                'class'     => \Milex\CoreBundle\Helper\Update\Github\ReleaseParser::class,
                'arguments' => [
                    'milex.http.client',
                ],
            ],
            'milex.helper.cache' => [
                'class'     => \Milex\CoreBundle\Helper\CacheHelper::class,
                'arguments' => [
                    '%kernel.cache_dir%',
                    'session',
                    'milex.helper.paths',
                    'kernel',
                ],
            ],
            'milex.helper.templating' => [
                'class'     => 'Milex\CoreBundle\Helper\TemplatingHelper',
                'arguments' => [
                    'kernel',
                ],
            ],
            'milex.helper.theme' => [
                'class'     => \Milex\CoreBundle\Helper\ThemeHelper::class,
                'arguments' => [
                    'milex.helper.paths',
                    'milex.helper.templating',
                    'translator',
                    'milex.helper.core_parameters',
                    'milex.filesystem',
                    'symfony.finder',
                    'milex.integrations.helper.builder_integrations',
                ],
                'methodCalls' => [
                    'setDefaultTheme' => [
                        '%milex.theme%',
                    ],
                ],
            ],
            'milex.helper.encryption' => [
                'class'     => \Milex\CoreBundle\Helper\EncryptionHelper::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.cipher.openssl',
                ],
            ],
            'milex.helper.language' => [
                'class'     => \Milex\CoreBundle\Helper\LanguageHelper::class,
                'arguments' => [
                    'milex.helper.paths',
                    'monolog.logger.milex',
                    'milex.helper.core_parameters',
                    'milex.http.client',
                ],
            ],
            'milex.helper.url' => [
                'class'     => \Milex\CoreBundle\Helper\UrlHelper::class,
                'arguments' => [
                    'milex.http.client',
                    '%milex.link_shortener_url%',
                    'monolog.logger.milex',
                ],
            ],
            'milex.helper.export' => [
                'class'     => \Milex\CoreBundle\Helper\ExportHelper::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.helper.composer' => [
                'class'     => \Milex\CoreBundle\Helper\ComposerHelper::class,
                'arguments' => [
                    'kernel',
                    'monolog.logger.milex',
                ],
            ],
            // Menu
            'milex.helper.menu' => [
                'class'     => 'Milex\CoreBundle\Menu\MenuHelper',
                'arguments' => [
                    'milex.security',
                    'request_stack',
                    'milex.helper.core_parameters',
                    'milex.helper.integration',
                ],
            ],
            'milex.helper.hash' => [
                'class' => \Milex\CoreBundle\Helper\HashHelper\HashHelper::class,
            ],
            'milex.helper.random' => [
                'class' => \Milex\CoreBundle\Helper\RandomHelper\RandomHelper::class,
            ],
            'milex.helper.command' => [
                'class'     => \Milex\CoreBundle\Helper\CommandHelper::class,
                'arguments' => 'kernel',
            ],
            'milex.menu_renderer' => [
                'class'     => \Milex\CoreBundle\Menu\MenuRenderer::class,
                'arguments' => [
                    'knp_menu.matcher',
                    'milex.helper.templating',
                ],
                'tag'   => 'knp_menu.renderer',
                'alias' => 'milex',
            ],
            'milex.menu.builder' => [
                'class'     => \Milex\CoreBundle\Menu\MenuBuilder::class,
                'arguments' => [
                    'knp_menu.factory',
                    'knp_menu.matcher',
                    'event_dispatcher',
                    'milex.helper.menu',
                ],
            ],
            // IP Lookup
            'milex.ip_lookup.factory' => [
                'class'     => \Milex\CoreBundle\Factory\IpLookupFactory::class,
                'arguments' => [
                    '%milex.ip_lookup_services%',
                    'monolog.logger.milex',
                    'milex.http.client',
                    '%kernel.cache_dir%',
                ],
            ],
            'milex.ip_lookup' => [
                'class'     => \Milex\CoreBundle\IpLookup\AbstractLookup::class, // bogus just to make cache compilation happy
                'factory'   => ['@milex.ip_lookup.factory', 'getService'],
                'arguments' => [
                    '%milex.ip_lookup_service%',
                    '%milex.ip_lookup_auth%',
                    '%milex.ip_lookup_config%',
                    'milex.http.client',
                ],
            ],
            'milex.native.connector' => [
                'class'     => \Symfony\Contracts\HttpClient\HttpClientInterface::class,
                'factory'   => [Symfony\Component\HttpClient\HttpClient::class, 'create'],
            ],

            'twig.controller.exception.class' => 'Milex\CoreBundle\Controller\ExceptionController',

            // Form extensions
            'milex.form.extension.custom' => [
                'class'        => \Milex\CoreBundle\Form\Extension\CustomFormExtension::class,
                'arguments'    => [
                    'event_dispatcher',
                ],
                'tag'          => 'form.type_extension',
                'tagArguments' => [
                    'extended_type' => Symfony\Component\Form\Extension\Core\Type\FormType::class,
                ],
            ],

            // Twig
            'templating.twig.extension.slot' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\SlotExtension::class,
                'arguments' => [
                    'templating.helper.slots',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.asset' => [
                'class'     => 'Milex\CoreBundle\Templating\Twig\Extension\AssetExtension',
                'arguments' => [
                    'templating.helper.assets',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.menu' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\MenuExtension::class,
                'arguments' => [
                    'milex.helper.template.menu',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.gravatar' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\GravatarExtension::class,
                'arguments' => [
                    'milex.helper.template.gravatar',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.version' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\VersionExtension::class,
                'arguments' => [
                    'milex.helper.app_version',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.mautibot' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\MautibotExtension::class,
                'arguments' => [
                    'milex.helper.template.mautibot',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.formatter' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\FormatterExtension::class,
                'arguments' => [
                    'milex.helper.template.formatter',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.date' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\DateExtension::class,
                'arguments' => [
                    'milex.helper.template.date',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.button' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\ButtonExtension::class,
                'arguments' => [
                    'milex.helper.template.button',
                    'request_stack',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.content' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\ContentExtension::class,
                'arguments' => [
                    'milex.helper.template.content',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.numeric' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\NumericExtension::class,
                'tag'       => 'twig.extension',
            ],
            'templating.twig.extension.form' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\FormExtension::class,
                'tag'       => 'twig.extension',
            ],
            'templating.twig.extension.class' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\ClassExtension::class,
                'tag'       => 'twig.extension',
            ],
            'templating.twig.extension.security' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\SecurityExtension::class,
                'arguments' => [
                    'milex.helper.template.security',
                ],
                'tag'       => 'twig.extension',
            ],
            'templating.twig.extension.translator' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\TranslatorExtension::class,
                'arguments' => [
                    'milex.helper.template.translator',
                ],
                'tag'       => 'twig.extension',
            ],
            'templating.twig.extension.config' => [
                'class'     => \Milex\CoreBundle\Templating\Twig\Extension\ConfigExtension::class,
                'arguments' => [
                    'milex.helper.template.config',
                ],
                'tag'       => 'twig.extension',
            ],
            // Schema
            'milex.schema.helper.column' => [
                'class'     => 'Milex\CoreBundle\Doctrine\Helper\ColumnSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%milex.db_table_prefix%',
                ],
            ],
            'milex.schema.helper.index' => [
                'class'     => 'Milex\CoreBundle\Doctrine\Helper\IndexSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%milex.db_table_prefix%',
                ],
            ],
            'milex.schema.helper.table' => [
                'class'     => 'Milex\CoreBundle\Doctrine\Helper\TableSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%milex.db_table_prefix%',
                    'milex.schema.helper.column',
                ],
            ],
            'milex.form.list.validator.circular' => [
                'class'     => Milex\CoreBundle\Form\Validator\Constraints\CircularDependencyValidator::class,
                'arguments' => [
                    'milex.lead.model.list',
                    'request_stack',
                ],
                'tag' => 'validator.constraint_validator',
            ],
            'milex.maxmind.doNotSellList' => [
                'class'     => Milex\CoreBundle\IpLookup\DoNotSellList\MaxMindDoNotSellList::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            // Logger
            'milex.monolog.handler' => [
                'class'     => \Milex\CoreBundle\Monolog\Handler\FileLogHandler::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.monolog.fulltrace.formatter',
                ],
            ],

            // Update steps
            'milex.update.step_provider' => [
                'class' => \Milex\CoreBundle\Update\StepProvider::class,
            ],
            'milex.update.step.delete_cache' => [
                'class'     => \Milex\CoreBundle\Update\Step\DeleteCacheStep::class,
                'arguments' => [
                    'milex.helper.cache',
                    'translator',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.finalize' => [
                'class'     => \Milex\CoreBundle\Update\Step\FinalizeUpdateStep::class,
                'arguments' => [
                    'translator',
                    'milex.helper.paths',
                    'session',
                    'milex.helper.app_version',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.install_new_files' => [
                'class'     => \Milex\CoreBundle\Update\Step\InstallNewFilesStep::class,
                'arguments' => [
                    'translator',
                    'milex.helper.update',
                    'milex.helper.paths',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.remove_deleted_files' => [
                'class'     => \Milex\CoreBundle\Update\Step\RemoveDeletedFilesStep::class,
                'arguments' => [
                    'translator',
                    'milex.helper.paths',
                    'monolog.logger.milex',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.update_schema' => [
                'class'     => \Milex\CoreBundle\Update\Step\UpdateSchemaStep::class,
                'arguments' => [
                    'translator',
                    'service_container',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.update_translations' => [
                'class'     => \Milex\CoreBundle\Update\Step\UpdateTranslationsStep::class,
                'arguments' => [
                    'translator',
                    'milex.helper.language',
                    'monolog.logger.milex',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.step.checks' => [
                'class'     => \Milex\CoreBundle\Update\Step\PreUpdateChecksStep::class,
                'arguments' => [
                    'translator',
                    'milex.helper.update',
                ],
                'tag' => 'milex.update_step',
            ],
            'milex.update.checks.php' => [
                'class' => \Milex\CoreBundle\Helper\Update\PreUpdateChecks\CheckPhpVersion::class,
                'tag'   => 'milex.update_check',
            ],
            'milex.update.checks.database' => [
                'class'     => \Milex\CoreBundle\Helper\Update\PreUpdateChecks\CheckDatabaseDriverAndVersion::class,
                'arguments' => [
                    'doctrine.orm.default_entity_manager',
                ],
                'tag' => 'milex.update_check',
            ],
        ],
        'models' => [
            'milex.core.model.auditlog' => [
                'class' => 'Milex\CoreBundle\Model\AuditLogModel',
            ],
            'milex.core.model.notification' => [
                'class'     => 'Milex\CoreBundle\Model\NotificationModel',
                'arguments' => [
                    'milex.helper.paths',
                    'milex.helper.update',
                    'milex.helper.core_parameters',
                ],
                'methodCalls' => [
                    'setDisableUpdates' => [
                        '%milex.security.disableUpdates%',
                    ],
                ],
            ],
            'milex.core.model.form' => [
                'class' => 'Milex\CoreBundle\Model\FormModel',
            ],
        ],
        'validator' => [
            'milex.core.validator.file_upload' => [
                'class'     => \Milex\CoreBundle\Validator\FileUploadValidator::class,
                'arguments' => [
                    'translator',
                ],
            ],
        ],
    ],

    'ip_lookup_services' => [
        'extreme-ip' => [
            'display_name' => 'Extreme-IP',
            'class'        => 'Milex\CoreBundle\IpLookup\ExtremeIpLookup',
        ],
        'freegeoip' => [
            'display_name' => 'Ipstack.com',
            'class'        => 'Milex\CoreBundle\IpLookup\IpstackLookup',
        ],
        'geobytes' => [
            'display_name' => 'Geobytes',
            'class'        => 'Milex\CoreBundle\IpLookup\GeobytesLookup',
        ],
        'geoips' => [
            'display_name' => 'GeoIPs',
            'class'        => 'Milex\CoreBundle\IpLookup\GeoipsLookup',
        ],
        'ipinfodb' => [
            'display_name' => 'IPInfoDB',
            'class'        => 'Milex\CoreBundle\IpLookup\IpinfodbLookup',
        ],
        'maxmind_country' => [
            'display_name' => 'MaxMind - Country Geolocation',
            'class'        => 'Milex\CoreBundle\IpLookup\MaxmindCountryLookup',
        ],
        'maxmind_omni' => [
            'display_name' => 'MaxMind - Insights (formerly Omni]',
            'class'        => 'Milex\CoreBundle\IpLookup\MaxmindOmniLookup',
        ],
        'maxmind_precision' => [
            'display_name' => 'MaxMind - GeoIP2 Precision',
            'class'        => 'Milex\CoreBundle\IpLookup\MaxmindPrecisionLookup',
        ],
        'maxmind_download' => [
            'display_name' => 'MaxMind - GeoLite2 City Download',
            'class'        => 'Milex\CoreBundle\IpLookup\MaxmindDownloadLookup',
        ],
        'telize' => [
            'display_name' => 'Telize',
            'class'        => 'Milex\CoreBundle\IpLookup\TelizeLookup',
        ],
        'ip2loctionlocal' => [
            'display_name' => 'IP2Location Local Bin File',
            'class'        => 'Milex\CoreBundle\IpLookup\IP2LocationBinLookup',
        ],
        'ip2loctionapi' => [
            'display_name' => 'IP2Location Web Service',
            'class'        => 'Milex\CoreBundle\IpLookup\IP2LocationAPILookup',
        ],
    ],

    'parameters' => [
        'site_url'                        => '',
        'webroot'                         => '',
        '404_page'                        => '',
        'cache_path'                      => '%kernel.root_dir%/../var/cache',
        'log_path'                        => '%kernel.root_dir%/../var/logs',
        'max_log_files'                   => 7,
        'log_file_name'                   => 'milex_%kernel.environment%.php',
        'image_path'                      => 'media/images',
        'tmp_path'                        => '%kernel.root_dir%/../var/tmp',
        'theme'                           => 'blank',
        'theme_import_allowed_extensions' => ['json', 'twig', 'css', 'js', 'htm', 'html', 'txt', 'jpg', 'jpeg', 'png', 'gif'],
        'db_driver'                       => 'pdo_mysql',
        'db_host'                         => '127.0.0.1',
        'db_port'                         => 3306,
        'db_name'                         => '',
        'db_user'                         => '',
        'db_password'                     => '',
        'db_table_prefix'                 => '',
        'locale'                          => 'en_US',
        'secret_key'                      => 'temp',
        'dev_hosts'                       => [],
        'trusted_hosts'                   => [],
        'trusted_proxies'                 => [],
        'rememberme_key'                  => hash('sha1', uniqid(mt_rand())),
        'rememberme_lifetime'             => 31536000, //365 days in seconds
        'rememberme_path'                 => '/',
        'rememberme_domain'               => '',
        'default_pagelimit'               => 30,
        'default_timezone'                => 'UTC',
        'date_format_full'                => 'F j, Y g:i a T',
        'date_format_short'               => 'D, M d',
        'date_format_dateonly'            => 'F j, Y',
        'date_format_timeonly'            => 'g:i a',
        'ip_lookup_service'               => 'maxmind_download',
        'ip_lookup_auth'                  => '',
        'ip_lookup_config'                => [],
        'ip_lookup_create_organization'   => false,
        'transifex_username'              => '',
        'transifex_password'              => '',
        'update_stability'                => 'stable',
        'cookie_path'                     => '/',
        'cookie_domain'                   => '',
        'cookie_secure'                   => true,
        'cookie_httponly'                 => false,
        'do_not_track_ips'                => [],
        'do_not_track_bots'               => [
            'MSNBOT',
            'msnbot-media',
            'bingbot',
            'Googlebot',
            'Google Web Preview',
            'Mediapartners-Google',
            'Baiduspider',
            'Ezooms',
            'YahooSeeker',
            'Slurp',
            'AltaVista',
            'AVSearch',
            'Mercator',
            'Scooter',
            'InfoSeek',
            'Ultraseek',
            'Lycos',
            'Wget',
            'YandexBot',
            'Java/1.4.1_04',
            'SiteBot',
            'Exabot',
            'AhrefsBot',
            'MJ12bot',
            'NetSeer crawler',
            'TurnitinBot',
            'magpie-crawler',
            'Nutch Crawler',
            'CMS Crawler',
            'rogerbot',
            'Domnutch',
            'ssearch_bot',
            'XoviBot',
            'digincore',
            'fr-crawler',
            'SeznamBot',
            'Seznam screenshot-generator',
            'Facebot',
            'facebookexternalhit',
            'SimplePie',
            'Riddler',
            '007ac9 Crawler',
            '360Spider',
            'A6-Indexer',
            'ADmantX',
            'AHC',
            'AISearchBot',
            'APIs-Google',
            'Aboundex',
            'AddThis',
            'Adidxbot',
            'AdsBot-Google',
            'AdsTxtCrawler',
            'AdvBot',
            'Ahrefs',
            'AlphaBot',
            'Amazon CloudFront',
            'AndersPinkBot',
            'Apache-HttpClient',
            'Apercite',
            'AppEngine-Google',
            'Applebot',
            'ArchiveBot',
            'BDCbot',
            'BIGLOTRON',
            'BLEXBot',
            'BLP_bbot',
            'BTWebClient',
            'BUbiNG',
            'Baidu-YunGuanCe',
            'Barkrowler',
            'BehloolBot',
            'BingPreview',
            'BomboraBot',
            'Bot.AraTurka.com',
            'BoxcarBot',
            'BrandVerity',
            'Buck',
            'CC Metadata Scaper',
            'CCBot',
            'CapsuleChecker',
            'Cliqzbot',
            'CloudFlare-AlwaysOnline',
            'Companybook-Crawler',
            'ContextAd Bot',
            'CrunchBot',
            'CrystalSemanticsBot',
            'CyberPatrol',
            'DareBoost',
            'Datafeedwatch',
            'Daum',
            'DeuSu',
            'developers.google.com',
            'Diffbot',
            'Digg Deeper',
            'Digincore bot',
            'Discordbot',
            'Disqus',
            'DnyzBot',
            'Domain Re-Animator Bot',
            'DomainStatsBot',
            'DuckDuckBot',
            'DuckDuckGo-Favicons-Bot',
            'EZID',
            'Embedly',
            'EveryoneSocialBot',
            'ExtLinksBot',
            'FAST Enterprise Crawler',
            'FAST-WebCrawler',
            'Feedfetcher-Google',
            'Feedly',
            'Feedspotbot',
            'FemtosearchBot',
            'Fetch',
            'Fever',
            'Flamingo_SearchEngine',
            'FlipboardProxy',
            'Fyrebot',
            'GarlikCrawler',
            'Genieo',
            'Gigablast',
            'Gigabot',
            'GingerCrawler',
            'Gluten Free Crawler',
            'GnowitNewsbot',
            'Go-http-client',
            'Google-Adwords-Instant',
            'Gowikibot',
            'GrapeshotCrawler',
            'Grobbot',
            'HTTrack',
            'Hatena',
            'IAS crawler',
            'ICC-Crawler',
            'IndeedBot',
            'InterfaxScanBot',
            'IstellaBot',
            'James BOT',
            'Jamie\'s Spider',
            'Jetslide',
            'Jetty',
            'Jugendschutzprogramm-Crawler',
            'K7MLWCBot',
            'Kemvibot',
            'KosmioBot',
            'Landau-Media-Spider',
            'Laserlikebot',
            'Leikibot',
            'Linguee Bot',
            'LinkArchiver',
            'LinkedInBot',
            'LivelapBot',
            'Luminator-robots',
            'Mail.RU_Bot',
            'Mastodon',
            'MauiBot',
            'Mediatoolkitbot',
            'MegaIndex',
            'MeltwaterNews',
            'MetaJobBot',
            'MetaURI',
            'Miniflux',
            'MojeekBot',
            'Moreover',
            'MuckRack',
            'Multiviewbot',
            'NING',
            'NerdByNature.Bot',
            'NetcraftSurveyAgent',
            'Netvibes',
            'Nimbostratus-Bot',
            'Nuzzel',
            'Ocarinabot',
            'OpenHoseBot',
            'OrangeBot',
            'OutclicksBot',
            'PR-CY.RU',
            'PaperLiBot',
            'Pcore-HTTP',
            'PhantomJS',
            'PiplBot',
            'PocketParser',
            'Primalbot',
            'PrivacyAwareBot',
            'Pulsepoint',
            'Python-urllib',
            'Qwantify',
            'RankActiveLinkBot',
            'RetrevoPageAnalyzer',
            'SBL-BOT',
            'SEMrushBot',
            'SEOkicks',
            'SWIMGBot',
            'SafeDNSBot',
            'SafeSearch microdata crawler',
            'ScoutJet',
            'Scrapy',
            'Screaming Frog SEO Spider',
            'SemanticScholarBot',
            'SimpleCrawler',
            'Siteimprove.com',
            'SkypeUriPreview',
            'Slack-ImgProxy',
            'Slackbot',
            'Snacktory',
            'SocialRankIOBot',
            'Sogou',
            'Sonic',
            'StorygizeBot',
            'SurveyBot',
            'Sysomos',
            'TangibleeBot',
            'TelegramBot',
            'Teoma',
            'Thinklab',
            'TinEye',
            'ToutiaoSpider',
            'Traackr.com',
            'Trove',
            'TweetmemeBot',
            'Twitterbot',
            'Twurly',
            'Upflow',
            'UptimeRobot',
            'UsineNouvelleCrawler',
            'Veoozbot',
            'WeSEE:Search',
            'WhatsApp',
            'Xenu Link Sleuth',
            'Y!J',
            'YaK',
            'Yahoo Link Preview',
            'Yeti',
            'YisouSpider',
            'Zabbix',
            'ZoominfoBot',
            'ZumBot',
            'ZuperlistBot',
            '^LCC ',
            'acapbot',
            'acoonbot',
            'adbeat_bot',
            'adscanner',
            'aiHitBot',
            'antibot',
            'arabot',
            'archive.org_bot',
            'axios',
            'backlinkcrawler',
            'betaBot',
            'bibnum.bnf',
            'binlar',
            'bitlybot',
            'blekkobot',
            'blogmuraBot',
            'bnf.fr_bot',
            'bot-pge.chlooe.com',
            'botify',
            'brainobot',
            'buzzbot',
            'cXensebot',
            'careerbot',
            'centurybot9',
            'changedetection',
            'check_http',
            'citeseerxbot',
            'coccoc',
            'collection@infegy.com',
            'content crawler spider',
            'contxbot',
            'convera',
            'crawler4j',
            'curl',
            'datagnionbot',
            'dcrawl',
            'deadlinkchecker',
            'discobot',
            'domaincrawler',
            'dotbot',
            'drupact',
            'ec2linkfinder',
            'edisterbot',
            'electricmonk',
            'elisabot',
            'epicbot',
            'eright',
            'europarchive.org',
            'exabot',
            'ezooms',
            'filterdb.iss.net',
            'findlink',
            'findthatfile',
            'findxbot',
            'fluffy',
            'fuelbot',
            'g00g1e.net',
            'g2reader-bot',
            'gnam gnam spider',
            'google-xrawler',
            'grub.org',
            'gslfbot',
            'heritrix',
            'http_get',
            'httpunit',
            'ia_archiver',
            'ichiro',
            'imrbot',
            'integromedb',
            'intelium_bot',
            'ip-web-crawler.com',
            'ips-agent',
            'iskanie',
            'it2media-domain-crawler',
            'jyxobot',
            'lb-spider',
            'libwww',
            'linkapediabot',
            'linkdex',
            'lipperhey',
            'lssbot',
            'lssrocketcrawler',
            'ltx71',
            'mappydata',
            'memorybot',
            'mindUpBot',
            'mlbot',
            'moatbot',
            'msnbot',
            'msrbot',
            'nerdybot',
            'netEstate NE Crawler',
            'netresearchserver',
            'newsharecounts',
            'newspaper',
            'niki-bot',
            'nutch',
            'okhttp',
            'omgili',
            'openindexspider',
            'page2rss',
            'panscient',
            'phpcrawl',
            'pingdom',
            'pinterest',
            'postrank',
            'proximic',
            'psbot',
            'purebot',
            'python-requests',
            'redditbot',
            'scribdbot',
            'seekbot',
            'semanticbot',
            'sentry',
            'seoscanners',
            'seznambot',
            'sistrix crawler',
            'sitebot',
            'siteexplorer.info',
            'smtbot',
            'spbot',
            'speedy',
            'summify',
            'tagoobot',
            'toplistbot',
            'tracemyfile',
            'trendictionbot',
            'turnitinbot',
            'twengabot',
            'um-LN',
            'urlappendbot',
            'vebidoobot',
            'vkShare',
            'voilabot',
            'wbsearchbot',
            'web-archive-net.com.bot',
            'webcompanycrawler',
            'webmon',
            'wget',
            'wocbot',
            'woobot',
            'woriobot',
            'wotbox',
            'xovibot',
            'yacybot',
            'yandex.com',
            'yanga',
            'yoozBot',
            'zgrab',
        ],
        'do_not_track_internal_ips' => [],
        'track_private_ip_ranges'   => false,
        'link_shortener_url'        => null,
        'cached_data_timeout'       => 10,
        'batch_sleep_time'          => 1,
        'batch_campaign_sleep_time' => false,
        'transliterate_page_title'  => false,
        'cors_restrict_domains'     => true,
        'cors_valid_domains'        => [],
        'max_entity_lock_time'      => 0,
        'default_daterange_filter'  => '-1 month',
        'debug'                     => false,
        'rss_notification_url'      => '',
        'translations_list_url'     => 'https://language-packs.milex.com/manifest.json',
        'translations_fetch_url'    => 'https://language-packs.milex.com/',
        'stats_update_url'          => 'https://updates.milex.org/stats/send', // set to empty in config file to disable
        'install_source'            => 'Milex',
        'system_update_url'         => 'https://api.github.com/repos/milex/milex/releases',
        'editor_fonts'              => [
            [
                'name' => 'Arial',
                'font' => 'Arial, Helvetica Neue, Helvetica, sans-serif',
            ],
            [
                'name' => 'Bitter',
                'font' => 'Bitter, Georgia, Times, Times New Roman, serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Bitter',
            ],
            [
                'name' => 'Courier New',
                'font' => 'Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace',
            ],
            [
                'name' => 'Droid Serif',
                'font' => 'Droid Serif, Georgia, Times, Times New Roman, serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Droid+Serif',
            ],
            [
                'name' => 'Georgia',
                'font' => 'Georgia, Times, Times New Roman, serif',
            ],
            [
                'name' => 'Helvetica',
                'font' => 'Helvetica Neue, Helvetica, Arial, sans-serif',
            ],
            [
                'name' => 'Lato',
                'font' => 'Lato, Tahoma, Verdana, Segoe, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Lato',
            ],
            [
                'name' => 'Lucida Sans Unicode',
                'font' => 'Lucida Sans Unicode, Lucida Grande, Lucida Sans, Geneva, Verdana, sans-serif',
            ],
            [
                'name' => 'Montserrat',
                'font' => 'Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Montserrat',
            ],
            [
                'name' => 'Open Sans',
                'font' => 'Open Sans, Helvetica Neue, Helvetica, Arial, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Open+Sans',
            ],
            [
                'name' => 'Roboto',
                'font' => 'Roboto, Tahoma, Verdana, Segoe, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Roboto',
            ],
            [
                'name' => 'Source Sans Pro',
                'font' => 'Source Sans Pro, Tahoma, Verdana, Segoe, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Source+Sans+Pro',
            ],
            [
                'name' => 'Tahoma',
                'font' => 'Tahoma, Geneva, Segoe, sans-serif',
            ],
            [
                'name' => 'Times New Roman',
                'font' => 'TimesNewRoman, Times New Roman, Times, Beskerville, Georgia, serif',
            ],
            [
                'name' => 'Trebuchet MS',
                'font' => 'Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif',
            ],
            [
                'name' => 'Ubuntu',
                'font' => 'Ubuntu, Tahoma, Verdana, Segoe, sans-serif',
                'url'  => 'https://fonts.googleapis.com/css?family=Ubuntu',
            ],
            [
                'name' => 'Verdana',
                'font' => 'Verdana, Geneva, sans-serif',
            ],
            [
                'name' => 'ヒラギノ角ゴ Pro W3',
                'font' => 'ヒラギノ角ゴ Pro W3, Hiragino Kaku Gothic Pro,Osaka, メイリオ, Meiryo, ＭＳ Ｐゴシック, MS PGothic, sans-serif',
            ],
            [
                'name' => 'メイリオ',
                'font' => 'メイリオ, Meiryo, ＭＳ Ｐゴシック, MS PGothic, ヒラギノ角ゴ Pro W3, Hiragino Kaku Gothic Pro,Osaka, sans-serif',
            ],
        ],
        'composer_updates' => false,
    ],
];
