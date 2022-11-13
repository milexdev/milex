<?php

return [
    'routes' => [
        'main' => [
            'milex_integration_auth_callback_secure' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MilexPluginBundle:Auth:authCallback',
            ],
            'milex_integration_auth_postauth_secure' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MilexPluginBundle:Auth:authStatus',
            ],
            'milex_plugin_index' => [
                'path'       => '/plugins',
                'controller' => 'MilexPluginBundle:Plugin:index',
            ],
            'milex_plugin_config' => [
                'path'       => '/plugins/config/{name}/{page}',
                'controller' => 'MilexPluginBundle:Plugin:config',
            ],
            'milex_plugin_info' => [
                'path'       => '/plugins/info/{name}',
                'controller' => 'MilexPluginBundle:Plugin:info',
            ],
            'milex_plugin_reload' => [
                'path'       => '/plugins/reload',
                'controller' => 'MilexPluginBundle:Plugin:reload',
            ],
        ],
        'public' => [
            'milex_integration_auth_user' => [
                'path'       => '/plugins/integrations/authuser/{integration}',
                'controller' => 'MilexPluginBundle:Auth:authUser',
            ],
            'milex_integration_auth_callback' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MilexPluginBundle:Auth:authCallback',
            ],
            'milex_integration_auth_postauth' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MilexPluginBundle:Auth:authStatus',
            ],
        ],
    ],
    'menu' => [
        'admin' => [
            'priority' => 50,
            'items'    => [
                'milex.plugin.plugins' => [
                    'id'        => 'milex_plugin_root',
                    'iconClass' => 'fa-plus-circle',
                    'access'    => 'plugin:plugins:manage',
                    'route'     => 'milex_plugin_index',
                ],
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.plugin.pointbundle.subscriber' => [
                'class' => \Milex\PluginBundle\EventListener\PointSubscriber::class,
            ],
            'milex.plugin.formbundle.subscriber' => [
                'class'       => \Milex\PluginBundle\EventListener\FormSubscriber::class,
                'methodCalls' => [
                    'setIntegrationHelper' => [
                        'milex.helper.integration',
                    ],
                ],
            ],
            'milex.plugin.campaignbundle.subscriber' => [
                'class'       => \Milex\PluginBundle\EventListener\CampaignSubscriber::class,
                'methodCalls' => [
                    'setIntegrationHelper' => [
                        'milex.helper.integration',
                    ],
                ],
            ],
            'milex.plugin.leadbundle.subscriber' => [
                'class'     => \Milex\PluginBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.plugin.model.plugin',
                ],
            ],
            'milex.plugin.integration.subscriber' => [
                'class'     => \Milex\PluginBundle\EventListener\IntegrationSubscriber::class,
                'arguments' => [
                    'monolog.logger.milex',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.integration.details' => [
                'class' => \Milex\PluginBundle\Form\Type\DetailsType::class,
            ],
            'milex.form.type.integration.settings' => [
                'class'     => \Milex\PluginBundle\Form\Type\FeatureSettingsType::class,
                'arguments' => [
                    'session',
                    'milex.helper.core_parameters',
                    'monolog.logger.milex',
                ],
            ],
            'milex.form.type.integration.fields' => [
                'class'     => \Milex\PluginBundle\Form\Type\FieldsType::class,
            ],
            'milex.form.type.integration.company.fields' => [
                'class'     => \Milex\PluginBundle\Form\Type\CompanyFieldsType::class,
            ],
            'milex.form.type.integration.keys' => [
                'class' => \Milex\PluginBundle\Form\Type\KeysType::class,
            ],
            'milex.form.type.integration.list' => [
                'class'     => \Milex\PluginBundle\Form\Type\IntegrationsListType::class,
                'arguments' => [
                    'milex.helper.integration',
                ],
            ],
            'milex.form.type.integration.config' => [
                'class' => \Milex\PluginBundle\Form\Type\IntegrationConfigType::class,
            ],
            'milex.form.type.integration.campaign' => [
                'class' => \Milex\PluginBundle\Form\Type\IntegrationCampaignsType::class,
            ],
        ],
        'other' => [
            'milex.helper.integration' => [
                'class'     => \Milex\PluginBundle\Helper\IntegrationHelper::class,
                'arguments' => [
                    'service_container',
                    'doctrine.orm.entity_manager',
                    'milex.helper.paths',
                    'milex.helper.bundle',
                    'milex.helper.core_parameters',
                    'milex.helper.templating',
                    'milex.plugin.model.plugin',
                ],
            ],
            'milex.plugin.helper.reload' => [
                'class'     => \Milex\PluginBundle\Helper\ReloadHelper::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.factory',
                ],
            ],
        ],
        'facades' => [
            'milex.plugin.facade.reload' => [
                'class'     => \Milex\PluginBundle\Facade\ReloadFacade::class,
                'arguments' => [
                    'milex.plugin.model.plugin',
                    'milex.plugin.helper.reload',
                    'translator',
                ],
            ],
        ],
        'models' => [
            'milex.plugin.model.plugin' => [
                'class'     => \Milex\PluginBundle\Model\PluginModel::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.helper.core_parameters',
                    'milex.helper.bundle',
                ],
            ],

            'milex.plugin.model.integration_entity' => [
                'class' => Milex\PluginBundle\Model\IntegrationEntityModel::class,
            ],
        ],
    ],
];
