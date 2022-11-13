<?php

namespace MilexPlugin\MilexSocialBundle\EventListener;

use Milex\ChannelBundle\ChannelEvents;
use Milex\ChannelBundle\Event\ChannelEvent;
use Milex\ChannelBundle\Model\MessageModel;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexSocialBundle\Form\Type\TweetListType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $helper;

    public function __construct(IntegrationHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 80],
        ];
    }

    public function onAddChannel(ChannelEvent $event)
    {
        $integration = $this->helper->getIntegrationObject('Twitter');
        if ($integration && $integration->getIntegrationSettings()->isPublished()) {
            $event->addChannel(
                'tweet',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'twitter.tweet',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => TweetListType::class,
                        'repository'     => 'MilexSocialBundle:Tweet',
                    ],
                ]
            );
        }
    }
}
