<?php

return [
    'name'        => 'FullContact',
    'description' => 'Enables integration with FullContact for contact and company lookup',
    'version'     => '1.0',
    'author'      => 'Milex',

    'routes' => [
        'public' => [
            'milex_plugin_fullcontact_index' => [
                'path'       => '/fullcontact/callback',
                'controller' => 'MilexFullContactBundle:Public:callback',
            ],
        ],
        'main' => [
            'milex_plugin_fullcontact_action' => [
                'path'       => '/fullcontact/{objectAction}/{objectId}',
                'controller' => 'MilexFullContactBundle:FullContact:execute',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.plugin.fullcontact.button.subscriber' => [
                'class'     => \MilexPlugin\MilexFullContactBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'milex.helper.integration',
                    'translator',
                    'router',
                ],
            ],
            'milex.plugin.fullcontact.lead.subscriber' => [
                'class'     => \MilexPlugin\MilexFullContactBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.plugin.fullcontact.lookup_helper',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.fullcontact_lookup' => [
                'class' => \MilexPlugin\MilexFullContactBundle\Form\Type\LookupType::class,
            ],
            'milex.form.type.fullcontact_batch_lookup' => [
                'class' => \MilexPlugin\MilexFullContactBundle\Form\Type\BatchLookupType::class,
            ],
        ],
        'others' => [
            'milex.plugin.fullcontact.lookup_helper' => [
                'class'     => 'MilexPlugin\MilexFullContactBundle\Helper\LookupHelper',
                'arguments' => [
                    'milex.helper.integration',
                    'milex.helper.user',
                    'monolog.logger.milex',
                    'router',
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                ],
            ],
        ],
        'integrations' => [
            'milex.integration.fullcontact' => [
                'class'     => \MilexPlugin\MilexFullContactBundle\Integration\FullContactIntegration::class,
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
];
