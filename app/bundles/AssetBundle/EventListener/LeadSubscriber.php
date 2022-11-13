<?php

namespace Milex\AssetBundle\EventListener;

use Milex\AssetBundle\Entity\DownloadRepository;
use Milex\AssetBundle\Model\AssetModel;
use Milex\LeadBundle\Event\LeadChangeEvent;
use Milex\LeadBundle\Event\LeadMergeEvent;
use Milex\LeadBundle\Event\LeadTimelineEvent;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var AssetModel
     */
    private $assetModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var DownloadRepository
     */
    private $downloadRepository;

    public function __construct(
        AssetModel $assetModel,
        TranslatorInterface $translator,
        RouterInterface $router,
        DownloadRepository $downloadRepository
    ) {
        $this->assetModel         = $assetModel;
        $this->translator         = $translator;
        $this->router             = $router;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
            LeadEvents::CURRENT_LEAD_CHANGED => ['onLeadChange', 0],
            LeadEvents::LEAD_POST_MERGE      => ['onLeadMerge', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     */
    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        // Set available event types
        $eventTypeKey  = 'asset.download';
        $eventTypeName = $this->translator->trans('milex.asset.event.download');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('assetList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        $downloads = $this->downloadRepository->getLeadDownloads($event->getLeadId(), $event->getQueryOptions());

        // Add total number to counter
        $event->addToCounter($eventTypeKey, $downloads);

        if (!$event->isEngagementCount()) {
            // Add the downloads to the event array
            foreach ($downloads['results'] as $download) {
                $asset = $this->assetModel->getEntity($download['asset_id']);
                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$download['download_id'],
                        'eventLabel' => [
                            'label' => $download['title'],
                            'href'  => $this->router->generate('milex_asset_action', ['objectAction' => 'view', 'objectId' => $download['asset_id']]),
                        ],
                        'extra' => [
                            'asset'            => $asset,
                            'assetDownloadUrl' => $this->assetModel->generateUrl($asset),
                        ],
                        'eventType'       => $eventTypeName,
                        'timestamp'       => $download['dateDownload'],
                        'icon'            => 'fa-download',
                        'contentTemplate' => 'MilexAssetBundle:SubscribedEvents\Timeline:index.html.php',
                        'contactId'       => $download['lead_id'],
                    ]
                );
            }
        }
    }

    public function onLeadChange(LeadChangeEvent $event)
    {
        $this->assetModel->getDownloadRepository()->updateLeadByTrackingId(
            $event->getNewLead()->getId(),
            $event->getNewTrackingId(),
            $event->getOldTrackingId()
        );
    }

    public function onLeadMerge(LeadMergeEvent $event)
    {
        $this->assetModel->getDownloadRepository()->updateLead($event->getLoser()->getId(), $event->getVictor()->getId());
    }
}
