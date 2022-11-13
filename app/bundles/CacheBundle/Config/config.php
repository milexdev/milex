<?php

declare(strict_types=1);

return [
    'routes'   => [
        'main'   => [],
        'public' => [],
        'api'    => [],
    ],
    'menu'     => [],
    'services' => [
        'events'    => [
            'milex.cache.clear_cache_subscriber' => [
                'class'     => \Milex\CacheBundle\EventListener\CacheClearSubscriber::class,
                'tags'      => ['kernel.cache_clearer'],
                'arguments' => [
                    'milex.cache.provider',
                    'monolog.logger.milex',
                ],
            ],
        ],
        'forms'     => [],
        'helpers'   => [],
        'menus'     => [],
        'other'     => [
            'milex.cache.provider'           => [
                'class'     => \Milex\CacheBundle\Cache\CacheProvider::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'service_container',
                ],
            ],
            'milex.cache.adapter.filesystem' => [
                'class'     => \Milex\CacheBundle\Cache\Adapter\FilesystemTagAwareAdapter::class,
                'arguments' => [
                    '%milex.cache_prefix%',
                    '%milex.cache_lifetime%',
                ],
                'tag'       => 'milex.cache.adapter',
            ],
            'milex.cache.adapter.memcached'  => [
                'class'     => \Milex\CacheBundle\Cache\Adapter\MemcachedTagAwareAdapter::class,
                'arguments' => [
                    '%milex.cache_adapter_memcached%',
                    '%milex.cache_prefix%',
                    '%milex.cache_lifetime%',
                ],
                'tag'       => 'milex.cache.adapter',
            ],
            'milex.cache.adapter.redis'      => [
                'class'     => \Milex\CacheBundle\Cache\Adapter\RedisTagAwareAdapter::class,
                'arguments' => [
                    '%milex.cache_adapter_redis%',
                    '%milex.cache_prefix%',
                    '%milex.cache_lifetime%',
                ],
                'tag'       => 'milex.cache.adapter',
            ],
        ],
        'models'    => [],
        'validator' => [],
    ],

    'parameters' => [
        'cache_adapter'           => 'milex.cache.adapter.filesystem',
        'cache_prefix'            => '',
        'cache_lifetime'          => 86400,
        'cache_adapter_memcached' => [
            'servers' => ['memcached://localhost'],
            'options' => [
                'compression'          => true,
                'libketama_compatible' => true,
                'serializer'           => 'igbinary',
            ],
        ],
        'cache_adapter_redis'     => [
            'dsn'     => 'redis://localhost',
            'options' => [
                'lazy'           => false,
                'persistent'     => 0,
                'persistent_id'  => null,
                'timeout'        => 30,
                'read_timeout'   => 0,
                'retry_interval' => 0,
            ],
        ],
    ],
];
