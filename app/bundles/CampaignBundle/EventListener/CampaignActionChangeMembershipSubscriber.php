<?php

namespace Milex\CampaignBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\PendingEvent;
use Milex\CampaignBundle\Form\Type\CampaignEventAddRemoveLeadType;
use Milex\CampaignBundle\Membership\MembershipManager;
use Milex\CampaignBundle\Model\CampaignModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignActionChangeMembershipSubscriber implements EventSubscriberInterface
{
    /**
     * @var MembershipManager
     */
    private $membershipManager;

    /**
     * @var CampaignModel
     */
    private $campaignModel;

    /**
     * CampaignActionChangeMembershipSubscriber constructor.
     */
    public function __construct(MembershipManager $membershipManager, CampaignModel $campaignModel)
    {
        $this->membershipManager = $membershipManager;
        $this->campaignModel     = $campaignModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD                    => ['addAction', 0],
            CampaignEvents::ON_CAMPAIGN_ACTION_CHANGE_MEMBERSHIP => ['changeMembership', 0],
        ];
    }

    /**
     * Add change membership action.
     */
    public function addAction(CampaignBuilderEvent $event)
    {
        $event->addAction(
            'campaign.addremovelead',
            [
                'label'           => 'milex.campaign.event.addremovelead',
                'description'     => 'milex.campaign.event.addremovelead_descr',
                'formType'        => CampaignEventAddRemoveLeadType::class,
                'formTypeOptions' => [
                    'include_this' => true,
                ],
                'batchEventName'  => CampaignEvents::ON_CAMPAIGN_ACTION_CHANGE_MEMBERSHIP,
            ]
        );
    }

    public function changeMembership(PendingEvent $event)
    {
        $properties          = $event->getEvent()->getProperties();
        $contacts            = $event->getContactsKeyedById();
        $executingCampaign   = $event->getEvent()->getCampaign();

        if (!empty($properties['addTo'])) {
            $campaigns = $this->getCampaigns($properties['addTo'], $executingCampaign);

            /** @var Campaign $campaign */
            foreach ($campaigns as $campaign) {
                $this->membershipManager->addContacts(
                    $contacts,
                    $campaign,
                    true
                );
            }
        }

        if (!empty($properties['removeFrom'])) {
            $campaigns = $this->getCampaigns($properties['removeFrom'], $executingCampaign);

            /** @var Campaign $campaign */
            foreach ($campaigns as $campaign) {
                $this->membershipManager->removeContacts(
                    $event->getContactsKeyedById(),
                    $campaign,
                    true
                );
            }
        }

        $event->passAll();
    }

    /**
     * @return array
     */
    private function getCampaigns(array $campaigns, Campaign $executingCampaign)
    {
        // Check for the keyword "this"
        $includeExecutingCampaign = false;
        $key                      = array_search('this', $campaigns);
        if (false !== $key) {
            $includeExecutingCampaign = true;
            // Remove it from the list of IDs
            unset($campaigns[$key]);
        }

        $campaignEntities = [];
        if (!empty($campaigns)) {
            $campaignEntities = $this->campaignModel->getEntities(['ids' => $campaigns, 'ignore_paginator' => true]);
        }

        // Include executing campaign if the keyword this was used
        if ($includeExecutingCampaign) {
            $campaignEntities[] = $executingCampaign;
        }

        return $campaignEntities;
    }
}
