<?php

namespace MilexPlugin\MilexSocialBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexSocialBundle\Form\Type\TweetSendType;
use MilexPlugin\MilexSocialBundle\Helper\CampaignEventHelper;
use MilexPlugin\MilexSocialBundle\SocialEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    /**
     * @var CampaignEventHelper
     */
    private $campaignEventHelper;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        CampaignEventHelper $campaignEventHelper,
        IntegrationHelper $integrationHelper,
        TranslatorInterface $translator
    ) {
        $this->campaignEventHelper = $campaignEventHelper;
        $this->integrationHelper   = $integrationHelper;
        $this->translator          = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Twitter');
        if ($integration && $integration->getIntegrationSettings()->isPublished()) {
            $action = [
                'label'           => 'milex.social.twitter.tweet.event.open',
                'description'     => 'milex.social.twitter.tweet.event.open_desc',
                'eventName'       => SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_channelId'],
                'formType'        => TweetSendType::class,
                'channel'         => 'social.tweet',
                'channelIdField'  => 'channelId',
            ];

            $event->addAction('twitter.tweet', $action);
        }
    }

    public function onCampaignAction(CampaignExecutionEvent $event)
    {
        $event->setChannel('social.twitter');
        if ($response = $this->campaignEventHelper->sendTweetAction($event->getLead(), $event->getEvent())) {
            return $event->setResult($response);
        }

        return $event->setFailed(
            $this->translator->trans('milex.social.twitter.error.handle_not_found')
        );
    }
}
