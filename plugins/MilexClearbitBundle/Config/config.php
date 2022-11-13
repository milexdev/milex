<?php

return [
    'name'        => 'Clearbit',
    'description' => 'Enables integration with Clearbit for contact and company lookup',
    'version'     => '1.0',
    'author'      => 'Werner Garcia',

    'routes' => [
        'public' => [
            'milex_plugin_clearbit_index' => [
                'path'       => '/clearbit/callback',
                'controller' => 'MilexClearbitBundle:Public:callback',
            ],
        ],
        'main' => [
            'milex_plugin_clearbit_action' => [
                'path'       => '/clearbit/{objectAction}/{objectId}',
                'controller' => 'MilexClearbitBundle:Clearbit:execute',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.plugin.clearbit.button.subscriber' => [
                'class'     => \MilexPlugin\MilexClearbitBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'milex.helper.integration',
                    'translator',
                    'router',
                ],
            ],
            'milex.plugin.clearbit.lead.subscriber' => [
                'class'     => \MilexPlugin\MilexClearbitBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.plugin.clearbit.lookup_helper',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.clearbit_lookup' => [
                'class' => 'MilexPlugin\MilexClearbitBundle\Form\Type\LookupType',
            ],
            'milex.form.type.clearbit_batch_lookup' => [
                'class' => 'MilexPlugin\MilexClearbitBundle\Form\Type\BatchLookupType',
            ],
        ],
        'others' => [
            'milex.plugin.clearbit.lookup_helper' => [
                'class'     => 'MilexPlugin\MilexClearbitBundle\Helper\LookupHelper',
                'arguments' => [
                    'milex.helper.integration',
                    'milex.helper.user',
                    'monolog.logger.milex',
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                ],
            ],
        ],
        'integrations' => [
            'milex.integration.clearbit' => [
                'class'     => \MilexPlugin\MilexClearbitBundle\Integration\ClearbitIntegration::class,
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
