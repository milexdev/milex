<?php

$container->loadFromExtension(
    'leezy_pheanstalk',
    [
        'pheanstalks' => [
            'primary' => [
                'server'  => '%milex.beanstalkd_host%',
                'port'    => '%milex.beanstalkd_port%',
                'timeout' => '%milex.beanstalkd_timeout%',
                'default' => true,
            ],
        ],
    ]
);
