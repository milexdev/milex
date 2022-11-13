<?php

namespace Milex\EmailBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Exception\InvalidEmailException;
use Milex\EmailBundle\Helper\EmailValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignConditionSubscriber implements EventSubscriberInterface
{
    /**
     * @var EmailValidator
     */
    private $validator;

    public function __construct(EmailValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD          => ['onCampaignBuild', 0],
            EmailEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $event->addCondition(
            'email.validate.address',
            [
                'label'       => 'milex.email.campaign.event.validate_address',
                'description' => 'milex.email.campaign.event.validate_address_descr',
                'eventName'   => EmailEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            ]
        );
    }

    public function onCampaignTriggerCondition(CampaignExecutionEvent $event)
    {
        try {
            $this->validator->validate($event->getLead()->getEmail(), true);
        } catch (InvalidEmailException $exception) {
            return $event->setResult(false);
        }

        return $event->setResult(true);
    }
}
