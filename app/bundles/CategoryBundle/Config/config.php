<?php

return [
    'routes' => [
        'main' => [
            'milex_category_batch_contact_set' => [
                'path'       => '/categories/batch/contact/set',
                'controller' => 'MilexCategoryBundle:BatchContact:exec',
            ],
            'milex_category_batch_contact_view' => [
                'path'       => '/categories/batch/contact/view',
                'controller' => 'MilexCategoryBundle:BatchContact:index',
            ],
            'milex_category_index' => [
                'path'       => '/categories/{bundle}/{page}',
                'controller' => 'MilexCategoryBundle:Category:index',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
            'milex_category_action' => [
                'path'       => '/categories/{bundle}/{objectAction}/{objectId}',
                'controller' => 'MilexCategoryBundle:Category:executeCategory',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
        ],
        'api' => [
            'milex_api_categoriesstandard' => [
                'standard_entity' => true,
                'name'            => 'categories',
                'path'            => '/categories',
                'controller'      => 'MilexCategoryBundle:Api\CategoryApi',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'milex.category.menu.index' => [
                'route'     => 'milex_category_index',
                'access'    => 'category:categories:view',
                'iconClass' => 'fa-folder',
                'id'        => 'milex_category_index',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.category.subscriber' => [
                'class'     => \Milex\CategoryBundle\EventListener\CategorySubscriber::class,
                'arguments' => [
                    'milex.helper.bundle',
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.category.button.subscriber' => [
                'class'     => \Milex\CategoryBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'router',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.category' => [
                'class'     => 'Milex\CategoryBundle\Form\Type\CategoryListType',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'translator',
                    'milex.category.model.category',
                    'router',
                ],
            ],
            'milex.form.type.category_form' => [
                'class'     => \Milex\CategoryBundle\Form\Type\CategoryType::class,
                'arguments' => [
                    'session',
                ],
            ],
            'milex.form.type.category_bundles_form' => [
                'class'     => 'Milex\CategoryBundle\Form\Type\CategoryBundlesType',
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
        ],
        'models' => [
            'milex.category.model.category' => [
                'class'     => 'Milex\CategoryBundle\Model\CategoryModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
            'milex.category.model.contact.action' => [
                'class'     => \Milex\CategoryBundle\Model\ContactActionModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                ],
            ],
        ],
    ],
];
