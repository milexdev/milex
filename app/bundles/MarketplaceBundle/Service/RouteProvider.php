<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Service;

use Symfony\Component\Routing\RouterInterface;

class RouteProvider
{
    public const ROUTE_LIST = 'milex_marketplace_list';

    public const ROUTE_DETAIL = 'milex_marketplace_detail';

    public const ROUTE_INSTALL = 'milex_marketplace_install';

    public const ROUTE_REMOVE = 'milex_marketplace_remove';

    public const ROUTE_CLEAR_CACHE = 'milex_marketplace_clear_cache';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildListRoute(int $page = 1): string
    {
        return $this->router->generate(static::ROUTE_LIST, ['page' => $page]);
    }

    public function buildDetailRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            static::ROUTE_DETAIL,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildInstallRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            static::ROUTE_DETAIL,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildRemoveRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            static::ROUTE_REMOVE,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildClearCacheRoute(): string
    {
        return $this->router->generate(
            static::ROUTE_CLEAR_CACHE
        );
    }
}
