<?php

declare(strict_types=1);

return [
    'name'        => 'GrapesJS Builder',
    'description' => 'GrapesJS Builder with MJML support for Milex',
    'version'     => '1.0.0',
    'author'      => 'Milex Community',
    'routes'      => [
        'main'   => [
            'grapesjsbuilder_upload' => [
                'path'       => '/grapesjsbuilder/upload',
                'controller' => 'GrapesJsBuilderBundle:FileManager:upload',
            ],
            'grapesjsbuilder_delete' => [
                'path'       => '/grapesjsbuilder/delete',
                'controller' => 'GrapesJsBuilderBundle:FileManager:delete',
            ],
            'grapesjsbuilder_builder' => [
                'path'       => '/grapesjsbuilder/{objectType}/{objectId}',
                'controller' => 'GrapesJsBuilderBundle:GrapesJs:builder',
            ],
        ],
        'public' => [],
        'api'    => [],
    ],
    'menu'        => [],
    'services'    => [
        'other'        => [
            // Provides access to configured API keys, settings, field mapping, etc
            'grapesjsbuilder.config' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\Integration\Config::class,
                'arguments' => [
                    'milex.integrations.helper',
                ],
            ],
        ],
        'sync'         => [],
        'integrations' => [
            // Basic definitions with name, display name and icon
            'milex.integration.grapesjsbuilder' => [
                'class' => \MilexPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration::class,
                'tags'  => [
                    'milex.integration',
                    'milex.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'grapesjsbuilder.integration.configuration' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\Integration\Support\ConfigSupport::class,
                'tags'      => [
                    'milex.config_integration',
                ],
            ],
            // Tells Milex what themes it should support when enabled
            'grapesjsbuilder.integration.builder' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\Integration\Support\BuilderSupport::class,
                'tags'      => [
                    'milex.builder_integration',
                ],
            ],
        ],
        'models'  => [
            'grapesjsbuilder.model' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel::class,
                'arguments' => [
                    'request_stack',
                    'milex.email.model.email',
                ],
            ],
        ],
        'helpers' => [
            'grapesjsbuilder.helper.filemanager' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\Helper\FileManager::class,
                'arguments' => [
                    'milex.helper.file_uploader',
                    'milex.helper.core_parameters',
                    'milex.helper.paths',
                ],
            ],
        ],
        'events'  => [
            'grapesjsbuilder.event.assets.subscriber' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\EventSubscriber\AssetsSubscriber::class,
                'arguments' => [
                    'grapesjsbuilder.config',
                    'milex.install.service',
                ],
            ],
            'grapesjsbuilder.event.email.subscriber' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\EventSubscriber\EmailSubscriber::class,
                'arguments' => [
                    'grapesjsbuilder.config',
                    'grapesjsbuilder.model',
                ],
            ],
            'grapesjsbuilder.event.content.subscriber' => [
                'class'     => \MilexPlugin\GrapesJsBuilderBundle\EventSubscriber\InjectCustomContentSubscriber::class,
                'arguments' => [
                    'grapesjsbuilder.config',
                    'grapesjsbuilder.model',
                    'grapesjsbuilder.helper.filemanager',
                    'milex.helper.templating',
                    'request_stack',
                    'router',
                ],
            ],
        ],
    ],
    'parameters' => [
        'image_path_exclude'     => ['flags', 'mejs'], // exclude certain folders from showing in the image browser
        'static_url'             => '', // optional base url for images
    ],
];
