<?php

declare(strict_types=1);

use Milex\MarketplaceBundle\Service\Config;
use Milex\MarketplaceBundle\Service\RouteProvider;

return [
    'routes' => [
        'main' => [
            RouteProvider::ROUTE_LIST => [
                'path'       => '/marketplace/{page}',
                'controller' => 'MarketplaceBundle:Package\List:list',
                'method'     => 'GET|POST',
                'defaults'   => ['page' => 1],
            ],
            RouteProvider::ROUTE_DETAIL => [
                'path'       => '/marketplace/detail/{vendor}/{package}',
                'controller' => 'MarketplaceBundle:Package\Detail:view',
                'method'     => 'GET',
            ],
            RouteProvider::ROUTE_INSTALL => [
                'path'       => '/marketplace/install/{vendor}/{package}',
                'controller' => 'MarketplaceBundle:Package\Install:view',
                'method'     => 'GET|POST',
            ],
            RouteProvider::ROUTE_REMOVE => [
                'path'       => '/marketplace/remove/{vendor}/{package}',
                'controller' => 'MarketplaceBundle:Package\Remove:view',
                'method'     => 'GET|POST',
            ],
            RouteProvider::ROUTE_CLEAR_CACHE => [
                'path'       => '/marketplace/clear/cache',
                'controller' => 'MarketplaceBundle:Cache:clear',
                'method'     => 'GET',
            ],
        ],
    ],
    'services' => [
        'controllers' => [
            'marketplace.controller.package.list' => [
                'class'     => \Milex\MarketplaceBundle\Controller\Package\ListController::class,
                'arguments' => [
                    'marketplace.service.plugin_collector',
                    'request_stack',
                    'marketplace.service.route_provider',
                    'milex.security',
                    'marketplace.service.config',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
            'marketplace.controller.package.detail' => [
                'class'     => \Milex\MarketplaceBundle\Controller\Package\DetailController::class,
                'arguments' => [
                    'marketplace.model.package',
                    'marketplace.service.route_provider',
                    'milex.security',
                    'marketplace.service.config',
                    'milex.helper.composer',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
            'marketplace.controller.package.install' => [
                'class'     => \Milex\MarketplaceBundle\Controller\Package\InstallController::class,
                'arguments' => [
                    'marketplace.model.package',
                    'marketplace.service.route_provider',
                    'milex.security',
                    'marketplace.service.config',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
            'marketplace.controller.package.remove' => [
                'class'     => \Milex\MarketplaceBundle\Controller\Package\RemoveController::class,
                'arguments' => [
                    'marketplace.model.package',
                    'marketplace.service.route_provider',
                    'milex.security',
                    'marketplace.service.config',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
            'marketplace.controller.cache' => [
                'class'     => \Milex\MarketplaceBundle\Controller\CacheController::class,
                'arguments' => [
                    'milex.security',
                    'marketplace.service.config',
                    'marketplace.service.allowlist',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
            'marketplace.controller.ajax' => [
                'class'     => \Milex\MarketplaceBundle\Controller\AjaxController::class,
                'arguments' => [
                    'milex.helper.composer',
                    'milex.helper.cache',
                    'monolog.logger.milex',
                ],
            ],
        ],
        'commands' => [
            'marketplace.command.list' => [
                'class'     => \Milex\MarketplaceBundle\Command\ListCommand::class,
                'tag'       => 'console.command',
                'arguments' => ['marketplace.service.plugin_collector'],
            ],
            'marketplace.command.install' => [
                'class'     => \Milex\MarketplaceBundle\Command\InstallCommand::class,
                'tag'       => 'console.command',
                'arguments' => ['milex.helper.composer', 'marketplace.model.package'],
            ],
            'marketplace.command.remove' => [
                'class'     => \Milex\MarketplaceBundle\Command\RemoveCommand::class,
                'tag'       => 'console.command',
                'arguments' => ['milex.helper.composer', 'monolog.logger.milex'],
            ],
        ],
        'events' => [
            'marketplace.menu.subscriber' => [
                'class'     => \Milex\MarketplaceBundle\EventListener\MenuSubscriber::class,
                'arguments' => [
                    'marketplace.service.config',
                ],
            ],
        ],
        'permissions' => [
            'marketplace.permissions' => [
                'class'     => \Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'marketplace.service.config',
                ],
            ],
        ],
        'api' => [
            'marketplace.api.connection' => [
                'class'     => \Milex\MarketplaceBundle\Api\Connection::class,
                'arguments' => [
                    'milex.http.client',
                    'monolog.logger.milex',
                ],
            ],
        ],
        'models' => [
            'marketplace.model.package' => [
                'class'     => \Milex\MarketplaceBundle\Model\PackageModel::class,
                'arguments' => ['marketplace.api.connection', 'marketplace.service.allowlist'],
            ],
        ],
        'other' => [
            'marketplace.service.plugin_collector' => [
                'class'     => \Milex\MarketplaceBundle\Service\PluginCollector::class,
                'arguments' => [
                    'marketplace.api.connection',
                    'marketplace.service.allowlist',
                ],
            ],
            'marketplace.service.route_provider' => [
                'class'     => \Milex\MarketplaceBundle\Service\RouteProvider::class,
                'arguments' => ['router'],
            ],
            'marketplace.service.config' => [
                'class'     => \Milex\MarketplaceBundle\Service\Config::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'marketplace.service.allowlist' => [
                'class'     => \Milex\MarketplaceBundle\Service\Allowlist::class,
                'arguments' => [
                    'marketplace.service.config',
                    'milex.cache.provider',
                    'milex.http.client',
                ],
            ],
        ],
    ],
    // NOTE: when adding new parameters here, please add them to the developer documentation as well:
    'parameters' => [
        Config::MARKETPLACE_ENABLED                     => true,
        Config::MARKETPLACE_ALLOWLIST_URL               => 'https://raw.githubusercontent.com/milex/marketplace-allowlist/main/allowlist.json',
        Config::MARKETPLACE_ALLOWLIST_CACHE_TTL_SECONDS => 3600,
    ],
];
