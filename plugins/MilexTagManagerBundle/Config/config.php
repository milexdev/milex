<?php

return [
    'name'        => 'Milex tag manager bundle',
    'description' => 'Provides an interface for tags management.',
    'version'     => '1.0',
    'author'      => 'Leuchtfeuer',
    'routes'      => [
        'main' => [
            'milex_tagmanager_index' => [
                'path'       => '/tags/{page}',
                'controller' => 'MilexTagManagerBundle:Tag:index',
            ],
            'milex_tagmanager_action' => [
                'path'       => '/tags/{objectAction}/{objectId}',
                'controller' => 'MilexTagManagerBundle:Tag:execute',
            ],
        ],
    ],
    'services'    => [
        'integrations' => [
            'milex.integration.tagmanager' => [
                'class'     => \MilexPlugin\MilexTagManagerBundle\Integration\TagManagerIntegration::class,
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
        'repositories' => [
            'milex.tagmanager.repository.tag' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \MilexPlugin\MilexTagManagerBundle\Entity\Tag::class,
                ],
            ],
        ],
        'models' => [
            'milex.tagmanager.model.tag' => [
                'class'     => \MilexPlugin\MilexTagManagerBundle\Model\TagModel::class,
                'arguments' => [
                    'service_container',
                ],
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'tagmanager.menu.index' => [
                'id'        => 'milex_tagmanager_index',
                'route'     => 'milex_tagmanager_index',
                'access'    => 'tagManager:tagManager:view',
                'iconClass' => 'fa-tag',
                'priority'  => 1,
            ],
        ],
    ],
  ];
