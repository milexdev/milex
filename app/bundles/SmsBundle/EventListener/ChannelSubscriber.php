<?php

namespace Milex\SmsBundle\EventListener;

use Milex\ChannelBundle\ChannelEvents;
use Milex\ChannelBundle\Event\ChannelEvent;
use Milex\ChannelBundle\Model\MessageModel;
use Milex\LeadBundle\Model\LeadModel;
use Milex\ReportBundle\Model\ReportModel;
use Milex\SmsBundle\Form\Type\SmsListType;
use Milex\SmsBundle\Sms\TransportChain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelSubscriber implements EventSubscriberInterface
{
    /**
     * @var TransportChain
     */
    private $transportChain;

    public function __construct(TransportChain $transportChain)
    {
        $this->transportChain = $transportChain;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 90],
        ];
    }

    public function onAddChannel(ChannelEvent $event)
    {
        if (count($this->transportChain->getEnabledTransports()) > 0) {
            $event->addChannel(
                'sms',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'sms.send_text_sms',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => SmsListType::class,
                        'repository'     => 'MilexSmsBundle:Sms',
                    ],
                    LeadModel::CHANNEL_FEATURE   => [],
                    ReportModel::CHANNEL_FEATURE => [
                        'table' => 'sms_messages',
                    ],
                ]
            );
        }
    }
}
