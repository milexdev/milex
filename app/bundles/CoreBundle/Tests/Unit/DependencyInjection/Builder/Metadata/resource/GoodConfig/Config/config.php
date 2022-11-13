<?php

return [
    'routes'   => [
        'main' => [
            'milex_core_ajax' => [
                'path'       => '/ajax',
                'controller' => 'MilexCoreBundle:Ajax:delegateAjax',
            ],
        ],
    ],
    'menu'     => [
        'main' => [
            'milex.core.components' => [
                'id'        => 'milex_components_root',
                'iconClass' => 'fa-puzzle-piece',
                'priority'  => 60,
            ],
        ],
    ],
    'services' => [
        'helpers'  => [
            'milex.helper.bundle' => [
                'class'     => 'Milex\CoreBundle\Helper\BundleHelper',
                'arguments' => [
                    '%milex.bundles%',
                    '%milex.plugin.bundles%',
                ],
            ],
        ],
        'other'    => [
            'milex.http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
        ],
        'fixtures' => [
            'milex.test.fixture' => [
                'class'    => 'Foo\Bar\NonExisting',
                'optional' => true,
            ],
        ],
    ],

    'ip_lookup_services' => [
        'extreme-ip' => [
            'display_name' => 'Extreme-IP',
            'class'        => 'Milex\CoreBundle\IpLookup\ExtremeIpLookup',
        ],
    ],

    'parameters' => [
        'log_path'      => '%kernel.root_dir%/../var/logs',
        'max_log_files' => 7,
        'image_path'    => 'media/images',
        'bool_value'    => false,
        'null_value'    => null,
        'array_value'   => [],
    ],
];
