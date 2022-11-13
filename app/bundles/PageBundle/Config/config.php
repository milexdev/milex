<?php

return [
    'routes' => [
        'main' => [
            'milex_page_index' => [
                'path'       => '/pages/{page}',
                'controller' => 'MilexPageBundle:Page:index',
            ],
            'milex_page_action' => [
                'path'       => '/pages/{objectAction}/{objectId}',
                'controller' => 'MilexPageBundle:Page:execute',
            ],
            'milex_page_results' => [
                'path'       => '/pages/results/{objectId}/{page}',
                'controller' => 'MilexPageBundle:Page:results',
            ],
            'milex_page_export' => [
                'path'       => '/pages/results/{objectId}/export/{format}',
                'controller' => 'MilexPageBundle:Page:export',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
        ],
        'public' => [
            'milex_page_tracker' => [
                'path'       => '/mtracking.gif',
                'controller' => 'MilexPageBundle:Public:trackingImage',
            ],
            'milex_page_tracker_cors' => [
                'path'       => '/mtc/event',
                'controller' => 'MilexPageBundle:Public:tracking',
            ],
            'milex_page_tracker_getcontact' => [
                'path'       => '/mtc',
                'controller' => 'MilexPageBundle:Public:getContactId',
            ],
            'milex_url_redirect' => [
                'path'       => '/r/{redirectId}',
                'controller' => 'MilexPageBundle:Public:redirect',
            ],
            'milex_page_redirect' => [
                'path'       => '/redirect/{redirectId}',
                'controller' => 'MilexPageBundle:Public:redirect',
            ],
            'milex_page_preview' => [
                'path'       => '/page/preview/{id}',
                'controller' => 'MilexPageBundle:Public:preview',
            ],
            'milex_gated_video_hit' => [
                'path'       => '/video/hit',
                'controller' => 'MilexPageBundle:Public:hitVideo',
            ],
        ],
        'api' => [
            'milex_api_pagesstandard' => [
                'standard_entity' => true,
                'name'            => 'pages',
                'path'            => '/pages',
                'controller'      => 'MilexPageBundle:Api\PageApi',
            ],
        ],
        'catchall' => [
            'milex_page_public' => [
                'path'         => '/{slug}',
                'controller'   => 'MilexPageBundle:Public:index',
                'requirements' => [
                    'slug' => '^(?!(_(profiler|wdt)|css|images|js|favicon.ico|apps/bundles/|plugins/)).+',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'milex.page.pages' => [
                    'route'    => 'milex_page_index',
                    'access'   => ['page:pages:viewown', 'page:pages:viewother'],
                    'parent'   => 'milex.core.components',
                    'priority' => 100,
                ],
            ],
        ],
    ],

    'categories' => [
        'page' => null,
    ],

    'services' => [
        'events' => [
            'milex.page.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.page.model.page',
                    'monolog.logger.milex',
                    'milex.page.repository.hit',
                    'milex.page.repository.page',
                    'milex.page.repository.redirect',
                    'milex.lead.repository.lead',
                ],
            ],
            'milex.pagebuilder.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\BuilderSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'milex.page.helper.token',
                    'milex.helper.integration',
                    'milex.page.model.page',
                    'milex.helper.token_builder.factory',
                    'translator',
                    'doctrine.dbal.default_connection',
                    'milex.helper.templating',
                ],
            ],
            'milex.pagetoken.subscriber' => [
                'class' => \Milex\PageBundle\EventListener\TokenSubscriber::class,
            ],
            'milex.page.pointbundle.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\PointSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                ],
            ],
            'milex.page.reportbundle.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'milex.lead.model.company_report_data',
                    'milex.page.repository.hit',
                    'translator',
                ],
            ],
            'milex.page.campaignbundle.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.page.helper.tracking',
                    'milex.campaign.executioner.realtime',
                ],
            ],
            'milex.page.leadbundle.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.page.model.page',
                    'milex.page.model.video',
                    'translator',
                    'router',
                ],
                'methodCalls' => [
                    'setModelFactory' => ['milex.model.factory'],
                ],
            ],
            'milex.page.calendarbundle.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\CalendarSubscriber::class,
                'arguments' => [
                    'milex.page.model.page',
                    'doctrine.dbal.default_connection',
                    'milex.security',
                    'translator',
                    'router',
                ],
            ],
            'milex.page.configbundle.subscriber' => [
                'class' => \Milex\PageBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.page.search.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.helper.user',
                    'milex.page.model.page',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.page.webhook.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\WebhookSubscriber::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                ],
            ],
            'milex.page.dashboard.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.page.model.page',
                    'router',
                ],
            ],
            'milex.page.js.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\BuildJsSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'milex.page.helper.tracking',
                    'router',
                ],
            ],
            'milex.page.maintenance.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\MaintenanceSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'translator',
                ],
            ],
            'milex.page.stats.subscriber' => [
                'class'     => \Milex\PageBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.page.subscriber.determine_winner' => [
                'class'     => \Milex\PageBundle\EventListener\DetermineWinnerSubscriber::class,
                'arguments' => [
                    'milex.page.repository.hit',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.page' => [
                'class'     => \Milex\PageBundle\Form\Type\PageType::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.page.model.page',
                    'milex.security',
                    'milex.helper.user',
                    'milex.helper.theme',
                ],
            ],
            'milex.form.type.pagevariant' => [
                'class'     => \Milex\PageBundle\Form\Type\VariantType::class,
                'arguments' => ['milex.page.model.page'],
            ],
            'milex.form.type.pointaction_pointhit' => [
                'class' => \Milex\PageBundle\Form\Type\PointActionPageHitType::class,
            ],
            'milex.form.type.pointaction_urlhit' => [
                'class' => \Milex\PageBundle\Form\Type\PointActionUrlHitType::class,
            ],
            'milex.form.type.pagehit.campaign_trigger' => [
                'class' => \Milex\PageBundle\Form\Type\CampaignEventPageHitType::class,
            ],
            'milex.form.type.pagelist' => [
                'class'     => \Milex\PageBundle\Form\Type\PageListType::class,
                'arguments' => [
                    'milex.page.model.page',
                    'milex.security',
                ],
            ],
            'milex.form.type.preferencecenterlist' => [
                'class'     => \Milex\PageBundle\Form\Type\PreferenceCenterListType::class,
                'arguments' => [
                    'milex.page.model.page',
                    'milex.security',
                ],
            ],
            'milex.form.type.page_abtest_settings' => [
                'class' => \Milex\PageBundle\Form\Type\AbTestPropertiesType::class,
            ],
            'milex.form.type.page_publish_dates' => [
                'class' => \Milex\PageBundle\Form\Type\PagePublishDatesType::class,
            ],
            'milex.form.type.pageconfig' => [
                'class' => \Milex\PageBundle\Form\Type\ConfigType::class,
            ],
            'milex.form.type.trackingconfig' => [
                'class' => \Milex\PageBundle\Form\Type\ConfigTrackingPageType::class,
            ],
            'milex.form.type.redirect_list' => [
                'class'     => \Milex\PageBundle\Form\Type\RedirectListType::class,
                'arguments' => ['milex.helper.core_parameters'],
            ],
            'milex.form.type.page_dashboard_hits_in_time_widget' => [
                'class' => \Milex\PageBundle\Form\Type\DashboardHitsInTimeWidgetType::class,
            ],
            'milex.page.tracking.pixel.send' => [
                'class'     => \Milex\PageBundle\Form\Type\TrackingPixelSendType::class,
                'arguments' => [
                    'milex.page.helper.tracking',
                ],
            ],
        ],
        'models' => [
            'milex.page.model.page' => [
                'class'     => \Milex\PageBundle\Model\PageModel::class,
                'arguments' => [
                    'milex.helper.cookie',
                    'milex.helper.ip_lookup',
                    'milex.lead.model.lead',
                    'milex.lead.model.field',
                    'milex.page.model.redirect',
                    'milex.page.model.trackable',
                    'milex.queue.service',
                    'milex.lead.model.company',
                    'milex.tracker.device',
                    'milex.tracker.contact',
                    'milex.helper.core_parameters',
                ],
                'methodCalls' => [
                    'setCatInUrl' => [
                        '%milex.cat_in_page_url%',
                    ],
                ],
            ],
            'milex.page.model.redirect' => [
                'class'     => 'Milex\PageBundle\Model\RedirectModel',
                'arguments' => [
                    'milex.helper.url',
                ],
            ],
            'milex.page.model.trackable' => [
                'class'     => \Milex\PageBundle\Model\TrackableModel::class,
                'arguments' => [
                    'milex.page.model.redirect',
                    'milex.lead.repository.field',
                ],
            ],
            'milex.page.model.video' => [
                'class'     => 'Milex\PageBundle\Model\VideoModel',
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.tracker.contact',
                ],
            ],
            'milex.page.model.tracking.404' => [
                'class'     => \Milex\PageBundle\Model\Tracking404Model::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.tracker.contact',
                    'milex.page.model.page',
                ],
            ],
        ],
        'repositories' => [
            'milex.page.repository.hit' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PageBundle\Entity\Hit::class,
                ],
            ],
            'milex.page.repository.page' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PageBundle\Entity\Page::class,
                ],
            ],
            'milex.page.repository.redirect' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PageBundle\Entity\Redirect::class,
                ],
            ],
        ],
        'fixtures' => [
            'milex.page.fixture.page_category' => [
                'class'     => \Milex\PageBundle\DataFixtures\ORM\LoadPageCategoryData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.category.model.category'],
            ],
            'milex.page.fixture.page' => [
                'class'     => \Milex\PageBundle\DataFixtures\ORM\LoadPageData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.page.model.page'],
            ],
            'milex.page.fixture.page_hit' => [
                'class'     => \Milex\PageBundle\DataFixtures\ORM\LoadPageHitData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.page.model.page'],
            ],
        ],
        'other' => [
            'milex.page.helper.token' => [
                'class'     => 'Milex\PageBundle\Helper\TokenHelper',
                'arguments' => 'milex.page.model.page',
            ],
            'milex.page.helper.tracking' => [
                'class'     => 'Milex\PageBundle\Helper\TrackingHelper',
                'arguments' => [
                    'session',
                    'milex.helper.core_parameters',
                    'request_stack',
                    'milex.tracker.contact',
                ],
            ],
        ],
    ],

    'parameters' => [
        'cat_in_page_url'       => false,
        'google_analytics'      => null,
        'track_contact_by_ip'   => false,
        'track_by_tracking_url' => false,
        'redirect_list_types'   => [
            '301' => 'milex.page.form.redirecttype.permanent',
            '302' => 'milex.page.form.redirecttype.temporary',
        ],
        'google_analytics_id'                   => null,
        'google_analytics_trackingpage_enabled' => false,
        'google_analytics_landingpage_enabled'  => false,
        'google_analytics_anonymize_ip'         => false,
        'facebook_pixel_id'                     => null,
        'facebook_pixel_trackingpage_enabled'   => false,
        'facebook_pixel_landingpage_enabled'    => false,
        'do_not_track_404_anonymous'            => false,
    ],
];
