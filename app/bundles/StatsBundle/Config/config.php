<?php

return [
    'services' => [
        'other' => [
            'milex.stats.aggregate.collector' => [
                'class'     => \Milex\StatsBundle\Aggregate\Collector::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
        ],
    ],
];
