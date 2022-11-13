<?php

return [
    'routes' => [
        'main' => [
            'milex_stage_index' => [
                'path'       => '/stages/{page}',
                'controller' => 'MilexStageBundle:Stage:index',
            ],
            'milex_stage_action' => [
                'path'       => '/stages/{objectAction}/{objectId}',
                'controller' => 'MilexStageBundle:Stage:execute',
            ],
        ],
        'api' => [
            'milex_api_stagesstandard' => [
                'standard_entity' => true,
                'name'            => 'stages',
                'path'            => '/stages',
                'controller'      => 'MilexStageBundle:Api\StageApi',
            ],
            'milex_api_stageddcontact' => [
                'path'       => '/stages/{id}/contact/{contactId}/add',
                'controller' => 'MilexStageBundle:Api\StageApi:addContact',
                'method'     => 'POST',
            ],
            'milex_api_stageremovecontact' => [
                'path'       => '/stages/{id}/contact/{contactId}/remove',
                'controller' => 'MilexStageBundle:Api\StageApi:removeContact',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'milex.stages.menu.index' => [
                'route'     => 'milex_stage_index',
                'iconClass' => 'fa-tachometer',
                'access'    => ['stage:stages:view'],
                'priority'  => 25,
            ],
        ],
    ],

    'categories' => [
        'stage' => null,
    ],

    'services' => [
        'events' => [
            'milex.stage.campaignbundle.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.stage.model.stage',
                    'translator',
                ],
            ],
            'milex.stage.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\StageSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.stage.leadbundle.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.lead.repository.stages_lead_log',
                    'milex.stage.repository.lead_stage_log',
                    'translator',
                    'router',
                ],
            ],
            'milex.stage.search.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.stage.model.stage',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.stage.dashboard.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.stage.model.stage',
                ],
            ],
            'milex.stage.stats.subscriber' => [
                'class'     => \Milex\StageBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'forms' => [
            'milex.stage.type.form' => [
                'class'     => \Milex\StageBundle\Form\Type\StageType::class,
                'arguments' => [
                    'milex.security',
                ],
            ],
            'milex.stage.type.action' => [
                'class' => 'Milex\StageBundle\Form\Type\StageActionType',
            ],
            'milex.stage.type.action_list' => [
                'class'     => 'Milex\StageBundle\Form\Type\StageActionListType',
                'arguments' => [
                    'milex.stage.model.stage',
                ],
            ],
            'milex.stage.type.action_change' => [
                'class' => 'Milex\StageBundle\Form\Type\StageActionChangeType',
            ],
            'milex.stage.type.stage_list' => [
                'class'     => 'Milex\StageBundle\Form\Type\StageListType',
                'arguments' => [
                    'milex.stage.model.stage',
                ],
            ],
            'milex.point.type.genericstage_settings' => [
                'class' => 'Milex\StageBundle\Form\Type\GenericStageSettingsType',
            ],
        ],
        'models' => [
            'milex.stage.model.stage' => [
                'class'     => 'Milex\StageBundle\Model\StageModel',
                'arguments' => [
                    'milex.lead.model.lead',
                    'session',
                    'milex.helper.user',
                ],
            ],
        ],
        'repositories' => [
            'milex.stage.repository.lead_stage_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\StageBundle\Entity\LeadStageLog::class,
                ],
            ],
        ],
    ],
];
