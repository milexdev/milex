<?php

namespace MilexPlugin\MilexCloudStorageBundle\EventListener;

use Milex\AssetBundle\AssetEvents;
use Milex\AssetBundle\Event as Events;
use MilexPlugin\MilexCloudStorageBundle\Integration\CloudStorageIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteAssetBrowseSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvents::ASSET_ON_REMOTE_BROWSE => ['onAssetRemoteBrowse', 0],
        ];
    }

    /**
     * Fetches the connector for an event's integration.
     */
    public function onAssetRemoteBrowse(Events\RemoteAssetBrowseEvent $event)
    {
        /** @var CloudStorageIntegration $integration */
        $integration = $event->getIntegration();

        $event->setAdapter($integration->getAdapter());
    }
}
