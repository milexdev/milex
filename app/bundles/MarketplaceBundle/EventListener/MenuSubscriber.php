<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\MenuEvent;
use Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use Milex\MarketplaceBundle\Service\Config;
use Milex\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MenuSubscriber implements EventSubscriberInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::BUILD_MENU => ['onBuildMenu', 9999],
        ];
    }

    public function onBuildMenu(MenuEvent $event): void
    {
        if ('admin' !== $event->getType() || !$this->config->marketplaceIsEnabled()) {
            return;
        }

        $event->addMenuItems(
            [
                'priority' => 81,
                'items'    => [
                    'marketplace.title' => [
                        'id'        => 'marketplace',
                        'route'     => RouteProvider::ROUTE_LIST,
                        'iconClass' => 'fa-plus',
                        'access'    => MarketplacePermissions::CAN_VIEW_PACKAGES,
                    ],
                ],
            ]
        );
    }
}
