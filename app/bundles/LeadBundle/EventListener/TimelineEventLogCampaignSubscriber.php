<?php

namespace Milex\LeadBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Event\CampaignLeadChangeEvent;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadEventLog;
use Milex\LeadBundle\Entity\LeadEventLogRepository;
use Milex\LeadBundle\Event\LeadTimelineEvent;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TimelineEventLogCampaignSubscriber implements EventSubscriberInterface
{
    use TimelineEventLogTrait;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * TimelineEventLogCampaignSubscriber constructor.
     */
    public function __construct(LeadEventLogRepository $eventLogRepository, UserHelper $userHelper, TranslatorInterface $translator)
    {
        $this->eventLogRepository = $eventLogRepository;
        $this->userHelper         = $userHelper;
        $this->translator         = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_LEADCHANGE     => 'onChange',
            CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE => 'onBatchChange',
            LeadEvents::TIMELINE_ON_GENERATE           => 'onTimelineGenerate',
        ];
    }

    public function onChange(CampaignLeadChangeEvent $event)
    {
        if (!$contact = $event->getLead()) {
            return;
        }

        $this->writeEntries(
            [$contact],
            $event->getCampaign(),
            $event->getAction()
        );
    }

    public function onBatchChange(CampaignLeadChangeEvent $event)
    {
        if (!$contacts = $event->getLeads()) {
            return;
        }

        $this->writeEntries(
            $contacts,
            $event->getCampaign(),
            $event->getAction()
        );
    }

    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $this->addEvents(
            $event,
            'campaign_membership',
            'milex.lead.timeline.campaign_membership',
            'fa-clock-o',
            'campaign',
            'campaign'
        );
    }

    /**
     * @param Lead[] $contacts
     * @param        $action
     */
    private function writeEntries(array $contacts, Campaign $campaign, $action)
    {
        $user = $this->userHelper->getUser();

        $logs = [];
        foreach ($contacts as $contact) {
            $log = new LeadEventLog();
            $log->setUserId($user->getId())
                ->setUserName($user->getUsername() ?: $this->translator->trans('milex.core.system'))
                ->setLead($contact)
                ->setBundle('campaign')
                ->setAction($action)
                ->setObject('campaign')
                ->setObjectId($campaign->getId())
                ->setProperties(
                    [
                        'campaign_id'        => $campaign->getId(),
                        'campaign_name'      => $campaign->getName(),
                        'object_description' => $campaign->getName(),
                    ]
                );

            $logs[] = $log;
        }

        $this->eventLogRepository->saveEntities($logs);
        $this->eventLogRepository->clear();
    }
}
