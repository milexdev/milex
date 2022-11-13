<?php

$container->loadFromExtension(
    'old_sound_rabbit_mq',
    [
        'connections' => [
            'default' => [
                'host'               => '%milex.rabbitmq_host%',
                'port'               => '%milex.rabbitmq_port%',
                'user'               => '%milex.rabbitmq_user%',
                'password'           => '%milex.rabbitmq_password%',
                'vhost'              => '%milex.rabbitmq_vhost%',
                'lazy'               => true,
                'connection_timeout' => 3,
                'heartbeat'          => 2,
                'read_write_timeout' => 4,
            ],
        ],
        'producers' => [
            'milex' => [
                'class'            => 'Milex\QueueBundle\Helper\RabbitMqProducer',
                'connection'       => 'default',
                'exchange_options' => [
                    'name'    => 'milex',
                    'type'    => 'direct',
                    'durable' => true,
                ],
                'queue_options' => [
                    'name'        => 'email_hit',
                    'auto_delete' => false,
                    'durable'     => true,
                ],
            ],
        ],
        'consumers' => [
            'milex' => [
                'connection'       => 'default',
                'exchange_options' => [
                    'name'    => 'milex',
                    'type'    => 'direct',
                    'durable' => true,
                ],
                'queue_options' => [
                    'name'        => 'email_hit',
                    'auto_delete' => false,
                    'durable'     => true,
                ],
                'callback'               => 'milex.queue.helper.rabbitmq_consumer',
                'idle_timeout'           => '%milex.rabbitmq_idle_timeout%',
                'idle_timeout_exit_code' => '%milex.rabbitmq_idle_timeout_exit_code%',
            ],
        ],
    ]
);
