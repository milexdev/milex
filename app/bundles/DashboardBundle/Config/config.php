<?php

return [
    'routes' => [
        'main' => [
            'milex_dashboard_index' => [
                'path'       => '/dashboard',
                'controller' => 'MilexDashboardBundle:Dashboard:index',
            ],
            'milex_dashboard_widget' => [
                'path'       => '/dashboard/widget/{widgetId}',
                'controller' => 'MilexDashboardBundle:Dashboard:widget',
            ],
            'milex_dashboard_action' => [
                'path'       => '/dashboard/{objectAction}/{objectId}',
                'controller' => 'MilexDashboardBundle:Dashboard:execute',
            ],
        ],
        'api' => [
            'milex_widget_types' => [
                'path'       => '/data',
                'controller' => 'MilexDashboardBundle:Api\WidgetApi:getTypes',
            ],
            'milex_widget_data' => [
                'path'       => '/data/{type}',
                'controller' => 'MilexDashboardBundle:Api\WidgetApi:getData',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'priority' => 100,
            'items'    => [
                'milex.dashboard.menu.index' => [
                    'route'     => 'milex_dashboard_index',
                    'iconClass' => 'fa-th-large',
                ],
            ],
        ],
    ],
    'services' => [
        'forms' => [
            'milex.dashboard.form.type.widget' => [
                'class'     => 'Milex\DashboardBundle\Form\Type\WidgetType',
                'arguments' => [
                    'event_dispatcher',
                    'milex.security',
                ],
            ],
        ],
        'models' => [
            'milex.dashboard.model.dashboard' => [
                'class'     => 'Milex\DashboardBundle\Model\DashboardModel',
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.helper.paths',
                    'symfony.filesystem',
                ],
            ],
        ],
        'other' => [
            'milex.dashboard.widget' => [
                'class'     => \Milex\DashboardBundle\Dashboard\Widget::class,
                'arguments' => [
                    'milex.dashboard.model.dashboard',
                    'milex.helper.user',
                    'session',
                ],
            ],
        ],
    ],
    'parameters' => [
        'dashboard_import_dir'      => '%kernel.root_dir%/../media/dashboards',
        'dashboard_import_user_dir' => null,
    ],
];
