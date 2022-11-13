<?php

namespace Milex\AssetBundle\EventListener;

use Milex\AssetBundle\AssetEvents;
use Milex\AssetBundle\Event\AssetLoadEvent;
use Milex\AssetBundle\Form\Type\PointActionAssetDownloadType;
use Milex\PointBundle\Event\PointBuilderEvent;
use Milex\PointBundle\Model\PointModel;
use Milex\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PointSubscriber implements EventSubscriberInterface
{
    /**
     * @var PointModel
     */
    private $pointModel;

    public function __construct(PointModel $pointModel)
    {
        $this->pointModel = $pointModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            AssetEvents::ASSET_ON_LOAD  => ['onAssetDownload', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event)
    {
        $action = [
            'group'       => 'milex.asset.actions',
            'label'       => 'milex.asset.point.action.download',
            'description' => 'milex.asset.point.action.download_descr',
            'callback'    => ['\\Milex\\AssetBundle\\Helper\\PointActionHelper', 'validateAssetDownload'],
            'formType'    => PointActionAssetDownloadType::class,
        ];

        $event->addAction('asset.download', $action);
    }

    /**
     * Trigger point actions for asset download.
     */
    public function onAssetDownload(AssetLoadEvent $event)
    {
        $asset = $event->getRecord()->getAsset();

        if (null !== $asset) {
            $this->pointModel->triggerAction('asset.download', $asset);
        }
    }
}
