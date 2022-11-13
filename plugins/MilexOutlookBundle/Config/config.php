<?php

return [
    'name'        => 'Outlook',
    'description' => 'Enables integrations with Outlook for email tracking',
    'version'     => '1.0',
    'author'      => 'Milex',
    'services'    => [
        'integrations' => [
            'milex.integration.outlook' => [
                'class'     => \MilexPlugin\MilexOutlookBundle\Integration\OutlookIntegration::class,
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
