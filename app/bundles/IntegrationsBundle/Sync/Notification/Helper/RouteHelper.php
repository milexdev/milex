<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\Notification\Helper;

use Milex\IntegrationsBundle\Event\InternalObjectRouteEvent;
use Milex\IntegrationsBundle\IntegrationEvents;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteHelper
{
    /**
     * @var ObjectProvider
     */
    private $objectProvider;

    /**
     * @var RouEventDispatcherInterfaceter
     */
    private $dispatcher;

    public function __construct(
        ObjectProvider $objectProvider,
        EventDispatcherInterface $dispatcher
    ) {
        $this->objectProvider = $objectProvider;
        $this->dispatcher     = $dispatcher;
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getRoute(string $object, int $id): string
    {
        try {
            $event = new InternalObjectRouteEvent($this->objectProvider->getObjectByName($object), $id);
        } catch (ObjectNotFoundException $e) {
            // Throw this exception instead to keep BC.
            throw new ObjectNotSupportedException(MilexSyncDataExchange::NAME, $object);
        }

        $this->dispatcher->dispatch(IntegrationEvents::INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE, $event);

        return $event->getRoute();
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getLink(string $object, int $id, string $linkText): string
    {
        $route = $this->getRoute($object, $id);

        return sprintf('<a href="%s">%s</a>', $route, $linkText);
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getRoutes(string $object, array $ids): array
    {
        $routes = [];
        foreach ($ids as $id) {
            $routes[$id] = $this->getRoute($object, $id);
        }

        return $routes;
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getLinkCsv(string $object, array $ids): string
    {
        $links  = [];
        $routes = $this->getRoutes($object, $ids);
        foreach ($routes as $id => $route) {
            $links[] = sprintf('[<a href="%s">%s</a>]', $route, $id);
        }

        return implode(', ', $links);
    }
}
