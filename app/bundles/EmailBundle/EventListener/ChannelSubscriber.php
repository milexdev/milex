<?php

namespace Milex\EmailBundle\EventListener;

use Milex\ChannelBundle\ChannelEvents;
use Milex\ChannelBundle\Event\ChannelEvent;
use Milex\ChannelBundle\Model\MessageModel;
use Milex\EmailBundle\Form\Type\EmailListType;
use Milex\LeadBundle\Model\LeadModel;
use Milex\ReportBundle\Model\ReportModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

const CHANNEL_COLUMN_CATEGORY_ID     = 'category_id';
const CHANNEL_COLUMN_NAME            = 'name';
const CHANNEL_COLUMN_DESCRIPTION     = 'description';
const CHANNEL_COLUMN_DATE_ADDED      = 'date_added';
const CHANNEL_COLUMN_CREATED_BY      = 'created_by';
const CHANNEL_COLUMN_CREATED_BY_USER = 'created_by_user';

class ChannelSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 100],
        ];
    }

    public function onAddChannel(ChannelEvent $event)
    {
        $event->addChannel(
            'email',
            [
                MessageModel::CHANNEL_FEATURE => [
                    'campaignAction'             => 'email.send',
                    'campaignDecisionsSupported' => [
                        'email.open',
                        'page.pagehit',
                        'asset.download',
                        'form.submit',
                    ],
                    'lookupFormType' => EmailListType::class,
                ],
                LeadModel::CHANNEL_FEATURE   => [],
                ReportModel::CHANNEL_FEATURE => [
                    'table' => 'emails',
                ],
            ]
        );
    }
}
