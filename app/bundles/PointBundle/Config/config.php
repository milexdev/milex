<?php

return [
    'routes' => [
        'main' => [
            'milex_pointtriggerevent_action' => [
                'path'       => '/points/triggers/events/{objectAction}/{objectId}',
                'controller' => 'MilexPointBundle:TriggerEvent:execute',
            ],
            'milex_pointtrigger_index' => [
                'path'       => '/points/triggers/{page}',
                'controller' => 'MilexPointBundle:Trigger:index',
            ],
            'milex_pointtrigger_action' => [
                'path'       => '/points/triggers/{objectAction}/{objectId}',
                'controller' => 'MilexPointBundle:Trigger:execute',
            ],
            'milex_point_index' => [
                'path'       => '/points/{page}',
                'controller' => 'MilexPointBundle:Point:index',
            ],
            'milex_point_action' => [
                'path'       => '/points/{objectAction}/{objectId}',
                'controller' => 'MilexPointBundle:Point:execute',
            ],
        ],
        'api' => [
            'milex_api_pointactionsstandard' => [
                'standard_entity' => true,
                'name'            => 'points',
                'path'            => '/points',
                'controller'      => 'MilexPointBundle:Api\PointApi',
            ],
            'milex_api_getpointactiontypes' => [
                'path'       => '/points/actions/types',
                'controller' => 'MilexPointBundle:Api\PointApi:getPointActionTypes',
            ],
            'milex_api_pointtriggersstandard' => [
                'standard_entity' => true,
                'name'            => 'triggers',
                'path'            => '/points/triggers',
                'controller'      => 'MilexPointBundle:Api\TriggerApi',
            ],
            'milex_api_getpointtriggereventtypes' => [
                'path'       => '/points/triggers/events/types',
                'controller' => 'MilexPointBundle:Api\TriggerApi:getPointTriggerEventTypes',
            ],
            'milex_api_pointtriggerdeleteevents' => [
                'path'       => '/points/triggers/{triggerId}/events/delete',
                'controller' => 'MilexPointBundle:Api\TriggerApi:deletePointTriggerEvents',
                'method'     => 'DELETE',
            ],
            'milex_api_adjustcontactpoints' => [
                'path'       => '/contacts/{leadId}/points/{operator}/{delta}',
                'controller' => 'MilexPointBundle:Api\PointApi:adjustPoints',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'milex.points.menu.root' => [
                'id'        => 'milex_points_root',
                'iconClass' => 'fa-calculator',
                'access'    => ['point:points:view', 'point:triggers:view'],
                'priority'  => 30,
                'children'  => [
                    'milex.point.menu.index' => [
                        'route'  => 'milex_point_index',
                        'access' => 'point:points:view',
                    ],
                    'milex.point.trigger.menu.index' => [
                        'route'  => 'milex_pointtrigger_index',
                        'access' => 'point:triggers:view',
                    ],
                ],
            ],
        ],
    ],

    'categories' => [
        'point' => null,
    ],

    'services' => [
        'events' => [
            'milex.point.subscriber' => [
                'class'     => \Milex\PointBundle\EventListener\PointSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.point.leadbundle.subscriber' => [
                'class'     => \Milex\PointBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.point.model.trigger',
                    'translator',
                    'milex.lead.repository.points_change_log',
                    'milex.point.repository.lead_point_log',
                    'milex.point.repository.lead_trigger_log',
                ],
            ],
            'milex.point.search.subscriber' => [
                'class'     => \Milex\PointBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                    'milex.point.model.trigger',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.point.dashboard.subscriber' => [
                'class'     => \Milex\PointBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                ],
            ],
            'milex.point.stats.subscriber' => [
                'class'     => \Milex\PointBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'forms' => [
            'milex.point.type.form' => [
                'class'     => \Milex\PointBundle\Form\Type\PointType::class,
                'arguments' => ['milex.security'],
            ],
            'milex.point.type.action' => [
                'class' => \Milex\PointBundle\Form\Type\PointActionType::class,
            ],
            'milex.pointtrigger.type.form' => [
                'class'     => \Milex\PointBundle\Form\Type\TriggerType::class,
                'arguments' => [
                  'milex.security',
                ],
            ],
            'milex.pointtrigger.type.action' => [
                'class' => \Milex\PointBundle\Form\Type\TriggerEventType::class,
            ],
            'milex.point.type.genericpoint_settings' => [
                'class' => \Milex\PointBundle\Form\Type\GenericPointSettingsType::class,
            ],
        ],
        'models' => [
            'milex.point.model.point' => [
                'class'     => \Milex\PointBundle\Model\PointModel::class,
                'arguments' => [
                    'session',
                    'milex.helper.ip_lookup',
                    'milex.lead.model.lead',
                    'milex.factory',
                    'milex.tracker.contact',
                ],
            ],
            'milex.point.model.triggerevent' => [
                'class' => \Milex\PointBundle\Model\TriggerEventModel::class,
            ],
            'milex.point.model.trigger' => [
                'class'     => \Milex\PointBundle\Model\TriggerModel::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.lead.model.lead',
                    'milex.point.model.triggerevent',
                    'milex.factory',
                    'milex.tracker.contact',
                ],
            ],
        ],
        'repositories' => [
            'milex.point.repository.lead_point_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PointBundle\Entity\LeadPointLog::class,
                ],
            ],
            'milex.point.repository.lead_trigger_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\PointBundle\Entity\LeadTriggerLog::class,
                ],
            ],
        ],
    ],
];
