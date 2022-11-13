<?php

return [
    'name'        => 'Cloud Storage',
    'description' => 'Enables integrations with Milex supported cloud storage services.',
    'version'     => '1.0',
    'author'      => 'Milex',

    'services' => [
        'events' => [
            'milex.cloudstorage.remoteassetbrowse.subscriber' => [
                'class' => \MilexPlugin\MilexCloudStorageBundle\EventListener\RemoteAssetBrowseSubscriber::class,
            ],
        ],
        'integrations' => [
            'milex.integration.amazons3' => [
                'class'     => \MilexPlugin\MilexCloudStorageBundle\Integration\AmazonS3Integration::class,
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
