<?php

namespace Milex\LeadBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\PendingEvent;
use Milex\CampaignBundle\Helper\RemovedContactTracker;
use Milex\LeadBundle\LeadEvents;
use Milex\LeadBundle\Model\LeadModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignActionDeleteContactSubscriber implements EventSubscriberInterface
{
    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var RemovedContactTracker
     */
    private $removedContactTracker;

    /**
     * CampaignActionDeleteContactSubscriber constructor.
     */
    public function __construct(LeadModel $leadModel, RemovedContactTracker $removedContactTracker)
    {
        $this->leadModel             = $leadModel;
        $this->removedContactTracker = $removedContactTracker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD             => ['configureAction', 0],
            LeadEvents::ON_CAMPAIGN_ACTION_DELETE_CONTACT => ['deleteContacts', 0],
        ];
    }

    public function configureAction(CampaignBuilderEvent $event)
    {
        $event->addAction(
            'lead.deletecontact',
            [
                'label'                  => 'milex.lead.lead.events.delete',
                'description'            => 'milex.lead.lead.events.delete_descr',
                // Kept for BC in case plugins are listening to the shared trigger
                'eventName'              => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'batchEventName'         => LeadEvents::ON_CAMPAIGN_ACTION_DELETE_CONTACT,
                'connectionRestrictions' => [
                    'target' => [
                        'decision'  => ['none'],
                        'action'    => ['none'],
                        'condition' => ['none'],
                    ],
                ],
            ]
        );
    }

    public function deleteContacts(PendingEvent $event)
    {
        $contactIds = $event->getContactIds();

        $this->removedContactTracker->addRemovedContacts(
            $event->getEvent()->getCampaign()->getId(),
            $contactIds
        );

        $this->leadModel->deleteEntities($contactIds);

        $event->passAll();
    }
}
