<?php

return [
    'name'        => 'Milex Focus',
    'description' => 'Drive visitor\'s focus on your website with Milex Focus',
    'version'     => '1.0',
    'author'      => 'Milex, Inc',

    'routes' => [
        'main' => [
            'milex_focus_index' => [
                'path'       => '/focus/{page}',
                'controller' => 'MilexFocusBundle:Focus:index',
            ],
            'milex_focus_action' => [
                'path'       => '/focus/{objectAction}/{objectId}',
                'controller' => 'MilexFocusBundle:Focus:execute',
            ],
        ],
        'public' => [
            'milex_focus_generate' => [
                'path'       => '/focus/{id}.js',
                'controller' => 'MilexFocusBundle:Public:generate',
            ],
            'milex_focus_pixel' => [
                'path'       => '/focus/{id}/viewpixel.gif',
                'controller' => 'MilexFocusBundle:Public:viewPixel',
            ],
        ],
        'api' => [
            'milex_api_focusstandard' => [
                'standard_entity' => true,
                'name'            => 'focus',
                'path'            => '/focus',
                'controller'      => 'MilexFocusBundle:Api\FocusApi',
            ],
            'milex_api_focusjs' => [
                'path'       => '/focus/{id}/js',
                'controller' => 'MilexFocusBundle:Api\FocusApi:generateJs',
                'method'     => 'POST',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.focus.subscriber.form_bundle' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'milex.focus.model.focus',
                ],
            ],
            'milex.focus.subscriber.page_bundle' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'milex.focus.model.focus',
                    'router',
                    'milex.helper.token_builder.factory',
                ],
            ],
            'milex.focus.subscriber.stat' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\StatSubscriber::class,
                'arguments' => [
                    'milex.focus.model.focus',
                    'request_stack',
                ],
            ],
            'milex.focus.subscriber.focus' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\FocusSubscriber::class,
                'arguments' => [
                    'router',
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.page.model.trackable',
                    'milex.page.helper.token',
                    'milex.asset.helper.token',
                    'milex.focus.model.focus',
                    'request_stack',
                ],
            ],
            'milex.focus.stats.subscriber' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.focus.campaignbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexFocusBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.page.helper.tracking',
                    'router',
                ],
            ],
        ],
        'forms' => [
            'milex.focus.form.type.focus' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Form\Type\FocusType::class,
                'arguments' => 'milex.security',
            ],
            'milex.focus.form.type.focusshow_list' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Form\Type\FocusShowType::class,
                'arguments' => 'router',
            ],
            'milex.focus.form.type.focus_list' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Form\Type\FocusListType::class,
                'arguments' => 'milex.focus.model.focus',
            ],
        ],
        'models' => [
            'milex.focus.model.focus' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Model\FocusModel::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.page.model.trackable',
                    'milex.helper.templating',
                    'event_dispatcher',
                    'milex.lead.model.field',
                    'milex.tracker.contact',
                ],
            ],
        ],
        'other' => [
            'milex.focus.helper.token' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Helper\TokenHelper::class,
                'arguments' => [
                    'milex.focus.model.focus',
                    'router',
                    'milex.security',
                ],
            ],
            'milex.focus.helper.iframe_availability_checker' => [
                'class'     => \MilexPlugin\MilexFocusBundle\Helper\IframeAvailabilityChecker::class,
                'arguments' => [
                    'translator',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'milex.focus' => [
                'route'    => 'milex_focus_index',
                'access'   => 'focus:items:view',
                'parent'   => 'milex.core.channels',
                'priority' => 10,
            ],
        ],
    ],

    'categories' => [
        'plugin:focus' => 'milex.focus',
    ],

    'parameters' => [
        'website_snapshot_url' => 'https://milex.net/api/snapshot',
        'website_snapshot_key' => '',
    ],
];
