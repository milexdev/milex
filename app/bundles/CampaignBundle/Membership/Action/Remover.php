<?php

namespace Milex\CampaignBundle\Membership\Action;

use Milex\CampaignBundle\Entity\Lead as CampaignMember;
use Milex\CampaignBundle\Entity\LeadEventLogRepository;
use Milex\CampaignBundle\Entity\LeadRepository;
use Milex\CampaignBundle\Membership\Exception\ContactAlreadyRemovedFromCampaignException;
use Milex\CoreBundle\Templating\Helper\DateHelper;
use Symfony\Component\Translation\TranslatorInterface;

class Remover
{
    const NAME = 'removed';

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    /**
     * @var LeadEventLogRepository
     */
    private $leadEventLogRepository;

    /**
     * @var string
     */
    private $unscheduledMessage;

    /**
     * Remover constructor.
     */
    public function __construct(
        LeadRepository $leadRepository,
        LeadEventLogRepository $leadEventLogRepository,
        TranslatorInterface $translator,
        DateHelper $dateHelper
    ) {
        $this->leadRepository         = $leadRepository;
        $this->leadEventLogRepository = $leadEventLogRepository;

        $dateRemoved              = $dateHelper->toFull(new \DateTime());
        $this->unscheduledMessage = $translator->trans('milex.campaign.member.removed', ['%date%' => $dateRemoved]);
    }

    /**
     * @param bool $isExit
     *
     * @throws ContactAlreadyRemovedFromCampaignException
     */
    public function updateExistingMembership(CampaignMember $campaignMember, $isExit)
    {
        if ($isExit) {
            // Contact was removed by the change campaign action or a segment
            $campaignMember->setDateLastExited(new \DateTime());
        } else {
            $campaignMember->setDateLastExited(null);
        }

        if ($campaignMember->wasManuallyRemoved()) {
            $this->saveCampaignMember($campaignMember);

            // Contact was already removed from this campaign
            throw new ContactAlreadyRemovedFromCampaignException();
        }

        // Unschedule any scheduled events
        $this->leadEventLogRepository->unscheduleEvents($campaignMember, $this->unscheduledMessage);

        // Remove this contact from the campaign
        $campaignMember->setManuallyRemoved(true);
        $campaignMember->setManuallyAdded(false);

        $this->saveCampaignMember($campaignMember);
    }

    /**
     * @param $campaignMember
     */
    private function saveCampaignMember($campaignMember)
    {
        $this->leadRepository->saveEntity($campaignMember);
        $this->leadRepository->detachEntity($campaignMember);
    }
}
