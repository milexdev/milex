<?php

namespace Milex\AssetBundle\EventListener;

use Milex\AssetBundle\AssetEvents;
use Milex\AssetBundle\Event\AssetLoadEvent;
use Milex\AssetBundle\Form\Type\CampaignEventAssetDownloadType;
use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\CampaignBundle\Executioner\RealTimeExecutioner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    /**
     * @var RealTimeExecutioner
     */
    private $realTimeExecutioner;

    public function __construct(RealTimeExecutioner $realTimeExecutioner)
    {
        $this->realTimeExecutioner = $realTimeExecutioner;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD         => ['onCampaignBuild', 0],
            AssetEvents::ASSET_ON_LOAD                => ['onAssetDownload', 0],
            AssetEvents::ON_CAMPAIGN_TRIGGER_DECISION => ['onCampaignTriggerDecision', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $trigger = [
            'label'          => 'milex.asset.campaign.event.download',
            'description'    => 'milex.asset.campaign.event.download_descr',
            'eventName'      => AssetEvents::ON_CAMPAIGN_TRIGGER_DECISION,
            'formType'       => CampaignEventAssetDownloadType::class,
            'channel'        => 'asset',
            'channelIdField' => 'assets',
        ];

        $event->addDecision('asset.download', $trigger);
    }

    /**
     * Trigger point actions for asset download.
     */
    public function onAssetDownload(AssetLoadEvent $event)
    {
        $asset = $event->getRecord()->getAsset();

        if (null !== $asset) {
            $this->realTimeExecutioner->execute('asset.download', $asset, 'asset', $asset->getId());
        }
    }

    public function onCampaignTriggerDecision(CampaignExecutionEvent $event)
    {
        $eventDetails = $event->getEventDetails();

        if (null == $eventDetails) {
            return $event->setResult(true);
        }

        $assetId       = $eventDetails->getId();
        $limitToAssets = $event->getConfig()['assets'];

        if (!empty($limitToAssets) && !in_array($assetId, $limitToAssets)) {
            //no points change
            return $event->setResult(false);
        }

        $event->setResult(true);
    }
}
