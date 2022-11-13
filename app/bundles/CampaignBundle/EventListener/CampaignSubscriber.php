<?php

namespace Milex\CampaignBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Event as Events;
use Milex\CampaignBundle\Service\Campaign as CampaignService;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Model\AuditLogModel;
use Milex\CoreBundle\Service\FlashBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    /**
     * @var IpLookupHelper
     */
    private $ipLookupHelper;

    /**
     * @var AuditLogModel
     */
    private $auditLogModel;

    /**
     * @var CampaignService
     */
    private $campaignService;

    /**
     * @var FlashBag
     */
    private $flashBag;

    public function __construct(
        IpLookupHelper $ipLookupHelper,
        AuditLogModel $auditLogModel,
        CampaignService $campaignService,
        FlashBag $flashBag
    ) {
        $this->ipLookupHelper   = $ipLookupHelper;
        $this->auditLogModel    = $auditLogModel;
        $this->campaignService  = $campaignService;
        $this->flashBag         = $flashBag;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_POST_SAVE     => ['onCampaignPostSave', 0],
            CampaignEvents::CAMPAIGN_POST_DELETE   => ['onCampaignDelete', 0],
        ];
    }

    /**
     * Add an entry to the audit log.
     */
    public function onCampaignPostSave(Events\CampaignEvent $event)
    {
        $campaign = $event->getCampaign();
        $details  = $event->getChanges();

        if ($campaign->isPublished() && $this->campaignService->hasUnpublishedEmail($campaign->getId())) {
            $this->setUnpublishedMailFlashMessage($campaign);
        }

        //don't set leads
        unset($details['leads']);

        if (!empty($details)) {
            $log = [
                'bundle'    => 'campaign',
                'object'    => 'campaign',
                'objectId'  => $campaign->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a delete entry to the audit log.
     */
    public function onCampaignDelete(Events\CampaignEvent $event)
    {
        $campaign = $event->getCampaign();
        $log      = [
            'bundle'    => 'campaign',
            'object'    => 'campaign',
            'objectId'  => $campaign->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $campaign->getName()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    private function setUnpublishedMailFlashMessage(Campaign $campaign)
    {
        $this->flashBag->add(
            'milex.core.notice.campaign.unpublished.email',
            [
                '%name%' => $campaign->getName(),
            ]
        );
    }
}
