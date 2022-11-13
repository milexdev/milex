<?php

return [
    'menu' => [
        'main' => [
            'items' => [
                'milex.dynamicContent.dynamicContent' => [
                    'route'    => 'milex_dynamicContent_index',
                    'access'   => ['dynamiccontent:dynamiccontents:viewown', 'dynamiccontent:dynamiccontents:viewother'],
                    'parent'   => 'milex.core.components',
                    'priority' => 90,
                ],
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'milex_dynamicContent_index' => [
                'path'       => '/dwc/{page}',
                'controller' => 'MilexDynamicContentBundle:DynamicContent:index',
            ],
            'milex_dynamicContent_action' => [
                'path'       => '/dwc/{objectAction}/{objectId}',
                'controller' => 'MilexDynamicContentBundle:DynamicContent:execute',
            ],
        ],
        'public' => [
            'milex_api_dynamicContent_index' => [
                'path'       => '/dwc',
                'controller' => 'MilexDynamicContentBundle:DynamicContentApi:getEntities',
            ],
            'milex_api_dynamicContent_action' => [
                'path'       => '/dwc/{objectAlias}',
                'controller' => 'MilexDynamicContentBundle:DynamicContentApi:process',
            ],
        ],
        'api' => [
            'milex_api_dynamicContent_standard' => [
                'standard_entity' => true,
                'name'            => 'dynamicContents',
                'path'            => '/dynamiccontents',
                'controller'      => 'MilexDynamicContentBundle:Api\DynamicContentApi',
            ],
        ],
    ],
    'services' => [
        'events' => [
            'milex.dynamicContent.campaignbundle.subscriber' => [
                'class'     => \Milex\DynamicContentBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.dynamicContent.model.dynamicContent',
                    'session',
                    'event_dispatcher',
                ],
            ],
            'milex.dynamicContent.js.subscriber' => [
                'class'     => \Milex\DynamicContentBundle\EventListener\BuildJsSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'translator',
                    'request_stack',
                    'router',
                ],
            ],
            'milex.dynamicContent.subscriber' => [
                'class'     => \Milex\DynamicContentBundle\EventListener\DynamicContentSubscriber::class,
                'arguments' => [
                    'milex.page.model.trackable',
                    'milex.page.helper.token',
                    'milex.asset.helper.token',
                    'milex.form.helper.token',
                    'milex.focus.helper.token',
                    'milex.core.model.auditlog',
                    'milex.helper.dynamicContent',
                    'milex.dynamicContent.model.dynamicContent',
                    'milex.security',
                    'milex.tracker.contact',
                ],
            ],
            'milex.dynamicContent.subscriber.channel' => [
                'class' => \Milex\DynamicContentBundle\EventListener\ChannelSubscriber::class,
            ],
            'milex.dynamicContent.stats.subscriber' => [
                'class'     => \Milex\DynamicContentBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.dynamicContent.lead.subscriber' => [
                'class'     => \Milex\DynamicContentBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'translator',
                    'router',
                    'milex.dynamicContent.repository.stat',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.dwc' => [
                'class'     => 'Milex\DynamicContentBundle\Form\Type\DynamicContentType',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.lead.model.list',
                    'translator',
                    'milex.lead.model.lead',
                ],
            ],
            'milex.form.type.dwc_entry_filters' => [
                'class'     => 'Milex\DynamicContentBundle\Form\Type\DwcEntryFiltersType',
                'arguments' => [
                    'translator',
                ],
                'methodCalls' => [
                    'setConnection' => [
                        'database_connection',
                    ],
                ],
            ],
            'milex.form.type.dwcsend_list' => [
                'class'     => 'Milex\DynamicContentBundle\Form\Type\DynamicContentSendType',
                'arguments' => [
                    'router',
                ],
            ],
            'milex.form.type.dwcdecision_list' => [
                'class'     => 'Milex\DynamicContentBundle\Form\Type\DynamicContentDecisionType',
                'arguments' => [
                    'router',
                ],
            ],
            'milex.form.type.dwc_list' => [
                'class' => 'Milex\DynamicContentBundle\Form\Type\DynamicContentListType',
            ],
        ],
        'models' => [
            'milex.dynamicContent.model.dynamicContent' => [
                'class'     => 'Milex\DynamicContentBundle\Model\DynamicContentModel',
                'arguments' => [
                ],
            ],
        ],
        'repositories' => [
            'milex.dynamicContent.repository.stat' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => \Milex\DynamicContentBundle\Entity\Stat::class,
            ],
        ],
        'other' => [
            'milex.helper.dynamicContent' => [
                'class'     => \Milex\DynamicContentBundle\Helper\DynamicContentHelper::class,
                'arguments' => [
                    'milex.dynamicContent.model.dynamicContent',
                    'milex.campaign.executioner.realtime',
                    'event_dispatcher',
                    'milex.lead.model.lead',
                ],
            ],
        ],
    ],
];
