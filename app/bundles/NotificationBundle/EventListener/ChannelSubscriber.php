<?php

namespace Milex\NotificationBundle\EventListener;

use Milex\ChannelBundle\ChannelEvents;
use Milex\ChannelBundle\Event\ChannelEvent;
use Milex\ChannelBundle\Model\MessageModel;
use Milex\NotificationBundle\Form\Type\NotificationListType;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Milex\ReportBundle\Model\ReportModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 70],
        ];
    }

    public function onAddChannel(ChannelEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $event->addChannel(
                'notification',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'notification.send_notification',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => NotificationListType::class,
                        'repository'     => 'MilexNotificationBundle:Notification',
                        'lookupOptions'  => [
                            'mobile'  => false,
                            'desktop' => true,
                        ],
                    ],
                    ReportModel::CHANNEL_FEATURE => [
                        'table' => 'push_notifications',
                    ],
                ]
            );

            $supportedFeatures = $integration->getSupportedFeatures();

            if (in_array('mobile', $supportedFeatures)) {
                $event->addChannel(
                    'mobile_notification',
                    [
                        MessageModel::CHANNEL_FEATURE => [
                            'campaignAction'             => 'notification.send_mobile_notification',
                            'campaignDecisionsSupported' => [
                                'page.pagehit',
                                'asset.download',
                                'form.submit',
                            ],
                            'lookupFormType'             => NotificationListType::class,
                            'repository'                 => 'MilexNotificationBundle:Notification',
                            'lookupOptions'              => [
                                'mobile'  => true,
                                'desktop' => false,
                            ],
                        ],
                    ]
                );
            }
        }
    }
}
