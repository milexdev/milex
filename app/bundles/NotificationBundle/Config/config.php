<?php

return [
    'services' => [
        'events' => [
            'milex.notification.campaignbundle.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.helper.integration',
                    'milex.notification.model.notification',
                    'milex.notification.api',
                    'event_dispatcher',
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.notification.campaignbundle.condition_subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\CampaignConditionSubscriber::class,
            ],
            'milex.notification.pagebundle.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'milex.helper.integration',
                ],
            ],
            'milex.core.js.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\BuildJsSubscriber::class,
                'arguments' => [
                    'milex.helper.notification',
                    'milex.helper.integration',
                    'router',
                ],
            ],
            'milex.notification.notificationbundle.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\NotificationSubscriber::class,
                'arguments' => [
                    'milex.core.model.auditlog',
                    'milex.page.model.trackable',
                    'milex.page.helper.token',
                    'milex.asset.helper.token',
                ],
            ],
            'milex.notification.subscriber.channel' => [
                'class'     => \Milex\NotificationBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'milex.helper.integration',
                ],
            ],
            'milex.notification.stats.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.notification.mobile_notification.report.subscriber' => [
                'class'     => \Milex\NotificationBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'milex.lead.model.company_report_data',
                    'milex.notification.repository.stat',
                ],
            ],
            'milex.notification.configbundle.subscriber' => [
                'class' => Milex\NotificationBundle\EventListener\ConfigSubscriber::class,
            ],
        ],
        'forms' => [
            'milex.form.type.notification' => [
                'class' => 'Milex\NotificationBundle\Form\Type\NotificationType',
            ],
            'milex.form.type.mobile.notification' => [
                'class' => \Milex\NotificationBundle\Form\Type\MobileNotificationType::class,
            ],
            'milex.form.type.mobile.notification_details' => [
                'class'     => \Milex\NotificationBundle\Form\Type\MobileNotificationDetailsType::class,
                'arguments' => [
                    'milex.helper.integration',
                ],
            ],
            'milex.form.type.notificationconfig' => [
                'class' => 'Milex\NotificationBundle\Form\Type\ConfigType',
            ],
            'milex.notification.config' => [
                'class' => \Milex\NotificationBundle\Form\Type\NotificationConfigType::class,
            ],
            'milex.form.type.notificationsend_list' => [
                'class'     => 'Milex\NotificationBundle\Form\Type\NotificationSendType',
                'arguments' => 'router',
            ],
            'milex.form.type.notification_list' => [
                'class' => 'Milex\NotificationBundle\Form\Type\NotificationListType',
            ],
            'milex.form.type.mobilenotificationsend_list' => [
                'class'     => \Milex\NotificationBundle\Form\Type\MobileNotificationSendType::class,
                'arguments' => 'router',
            ],
            'milex.form.type.mobilenotification_list' => [
                'class' => \Milex\NotificationBundle\Form\Type\MobileNotificationListType::class,
            ],
        ],
        'helpers' => [
            'milex.helper.notification' => [
                'class'     => 'Milex\NotificationBundle\Helper\NotificationHelper',
                'alias'     => 'notification_helper',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'templating.helper.assets',
                    'milex.helper.core_parameters',
                    'milex.helper.integration',
                    'router',
                    'request_stack',
                    'milex.lead.model.dnc',
                ],
            ],
        ],
        'other' => [
            'milex.notification.api' => [
                'class'     => \Milex\NotificationBundle\Api\OneSignalApi::class,
                'arguments' => [
                    'milex.http.client',
                    'milex.page.model.trackable',
                    'milex.helper.integration',
                ],
                'alias' => 'notification_api',
            ],
        ],
        'models' => [
            'milex.notification.model.notification' => [
                'class'     => 'Milex\NotificationBundle\Model\NotificationModel',
                'arguments' => [
                    'milex.page.model.trackable',
                ],
            ],
        ],
        'repositories' => [
            'milex.notification.repository.stat' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\NotificationBundle\Entity\Stat::class,
                ],
            ],
        ],
        'integrations' => [
            'milex.integration.onesignal' => [
                'class'     => \Milex\NotificationBundle\Integration\OneSignalIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'milex.helper.encryption',
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                    'milex.helper.paths',
                    'milex.core.model.notification',
                    'milex.lead.model.field',
                    'milex.plugin.model.integration_entity',
                    'milex.lead.model.dnc',
                ],
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'milex_notification_index' => [
                'path'       => '/notifications/{page}',
                'controller' => 'MilexNotificationBundle:Notification:index',
            ],
            'milex_notification_action' => [
                'path'       => '/notifications/{objectAction}/{objectId}',
                'controller' => 'MilexNotificationBundle:Notification:execute',
            ],
            'milex_notification_contacts' => [
                'path'       => '/notifications/view/{objectId}/contact/{page}',
                'controller' => 'MilexNotificationBundle:Notification:contacts',
            ],
            'milex_mobile_notification_index' => [
                'path'       => '/mobile_notifications/{page}',
                'controller' => 'MilexNotificationBundle:MobileNotification:index',
            ],
            'milex_mobile_notification_action' => [
                'path'       => '/mobile_notifications/{objectAction}/{objectId}',
                'controller' => 'MilexNotificationBundle:MobileNotification:execute',
            ],
            'milex_mobile_notification_contacts' => [
                'path'       => '/mobile_notifications/view/{objectId}/contact/{page}',
                'controller' => 'MilexNotificationBundle:MobileNotification:contacts',
            ],
        ],
        'public' => [
            'milex_receive_notification' => [
                'path'       => '/notification/receive',
                'controller' => 'MilexNotificationBundle:Api\NotificationApi:receive',
            ],
            'milex_subscribe_notification' => [
                'path'       => '/notification/subscribe',
                'controller' => 'MilexNotificationBundle:Api\NotificationApi:subscribe',
            ],
            'milex_notification_popup' => [
                'path'       => '/notification',
                'controller' => 'MilexNotificationBundle:Popup:index',
            ],

            // JS / Manifest URL's
            'milex_onesignal_worker' => [
                'path'       => '/OneSignalSDKWorker.js',
                'controller' => 'MilexNotificationBundle:Js:worker',
            ],
            'milex_onesignal_updater' => [
                'path'       => '/OneSignalSDKUpdaterWorker.js',
                'controller' => 'MilexNotificationBundle:Js:updater',
            ],
            'milex_onesignal_manifest' => [
                'path'       => '/manifest.json',
                'controller' => 'MilexNotificationBundle:Js:manifest',
            ],
            'milex_app_notification' => [
                'path'       => '/notification/appcallback',
                'controller' => 'MilexNotificationBundle:AppCallback:index',
            ],
        ],
        'api' => [
            'milex_api_notificationsstandard' => [
                'standard_entity' => true,
                'name'            => 'notifications',
                'path'            => '/notifications',
                'controller'      => 'MilexNotificationBundle:Api\NotificationApi',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'milex.notification.notifications' => [
                    'route'  => 'milex_notification_index',
                    'access' => ['notification:notifications:viewown', 'notification:notifications:viewother'],
                    'checks' => [
                        'integration' => [
                            'OneSignal' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'parent'   => 'milex.core.channels',
                    'priority' => 80,
                ],
                'milex.notification.mobile_notifications' => [
                    'route'  => 'milex_mobile_notification_index',
                    'access' => ['notification:mobile_notifications:viewown', 'notification:mobile_notifications:viewother'],
                    'checks' => [
                        'integration' => [
                            'OneSignal' => [
                                'enabled'  => true,
                                'features' => [
                                    'mobile',
                                ],
                            ],
                        ],
                    ],
                    'parent'   => 'milex.core.channels',
                    'priority' => 65,
                ],
            ],
        ],
    ],
    //'categories' => [
    //    'notification' => null
    //],
    'parameters' => [
        'notification_enabled'                        => false,
        'notification_landing_page_enabled'           => true,
        'notification_tracking_page_enabled'          => false,
        'notification_app_id'                         => null,
        'notification_rest_api_key'                   => null,
        'notification_safari_web_id'                  => null,
        'gcm_sender_id'                               => '482941778795',
        'notification_subdomain_name'                 => null,
        'welcomenotification_enabled'                 => true,
        'campaign_send_notification_to_author'        => true,
        'campaign_notification_email_addresses'       => null,
        'webhook_send_notification_to_author'         => true,
        'webhook_notification_email_addresses'        => null,
    ],
];
