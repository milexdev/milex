<?php

$loader->import('config.php');

if (file_exists(__DIR__.'/security_local.php')) {
    $loader->import('security_local.php');
} else {
    $loader->import('security.php');
}

/*
$container->loadFromExtension("framework", array(
    "validation" => array(
        "cache" => "apc"
    )
));

$container->loadFromExtension("doctrine", array(
    "orm" => array(
        "metadata_cache_driver" => "apc",
        "result_cache_driver"   => "apc",
        "query_cache_driver"    => "apc"
    )
));
*/

$container->loadFromExtension('monolog', [
    'channels' => [
        'milex',
    ],
    'handlers' => [
        'main' => [
            'type'         => 'fingers_crossed',
            'buffer_size'  => '200',
            'action_level' => 'error',
            'handler'      => 'nested',
            'channels'     => [
                '!milex',
            ],
        ],
        'nested' => [
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/%kernel.environment%.php',
            'level'     => 'error',
            'max_files' => 7,
        ],
        'milex' => [
            'type'      => 'service',
            'id'        => 'milex.monolog.handler',
            'channels'  => [
                'milex',
            ],
        ],
    ],
]);

//Twig Configuration
$container->loadFromExtension('twig', [
    'cache'       => '%env(resolve:MILEX_TWIG_CACHE_DIR)%',
    'auto_reload' => true,
    'paths'       => [
        '%kernel.root_dir%/bundles' => 'bundles',
    ],
    'form_themes' => [
        '@MilexCore/FormTheme/milex_form_layout.html.twig',
    ],
]);

// Allow overriding config without a requiring a full bundle or hacks
if (file_exists(__DIR__.'/config_override.php')) {
    $loader->import('config_override.php');
}

// Allow local settings without committing to git such as swift mailer delivery address overrides
if (file_exists(__DIR__.'/config_local.php')) {
    $loader->import('config_local.php');
}
