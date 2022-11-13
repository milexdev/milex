<?php

namespace Milex\NotificationBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\NotificationBundle\Entity\PushID;
use Milex\NotificationBundle\NotificationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CampaignConditionSubscriber.
 */
class CampaignConditionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD                 => ['onCampaignBuild', 0],
            NotificationEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerHasActiveCondition', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $event->addCondition(
            'notification.has.active',
            [
                'label'       => 'milex.notification.campaign.event.notification.has.active',
                'description' => 'milex.notification.campaign.event.notification.has.active.desc',
                'eventName'   => NotificationEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            ]
        );
    }

    public function onCampaignTriggerHasActiveCondition(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('notification.has.active')) {
            return;
        }

        $pushIds = $event->getLead()->getPushIDs();
        /** @var PushID $pushID */
        foreach ($pushIds as $pushID) {
            if ($pushID->isEnabled()) {
                return $event->setResult(true);
            }
        }

        return $event->setResult(false);
    }
}
