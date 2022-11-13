<?php

return [
    'routes' => [
        'main' => [
            'milex_calendar_index' => [
                'path'       => '/calendar',
                'controller' => 'MilexCalendarBundle:Default:index',
            ],
            'milex_calendar_action' => [
                'path'       => '/calendar/{objectAction}',
                'controller' => 'MilexCalendarBundle:Default:execute',
            ],
        ],
    ],
    'services' => [
        'models' => [
            'milex.calendar.model.calendar' => [
                'class' => 'Milex\CalendarBundle\Model\CalendarModel',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'priority' => 90,
            'items'    => [
                'milex.calendar.menu.index' => [
                    'route'     => 'milex_calendar_index',
                    'iconClass' => 'fa-calendar',
                ],
            ],
        ],
    ],
];
