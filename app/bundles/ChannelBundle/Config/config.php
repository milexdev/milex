<?php

return [
    'routes' => [
        'main' => [
            'milex_message_index' => [
                'path'       => '/messages/{page}',
                'controller' => 'MilexChannelBundle:Message:index',
            ],
            'milex_message_contacts' => [
                'path'       => '/messages/contacts/{objectId}/{channel}/{page}',
                'controller' => 'MilexChannelBundle:Message:contacts',
            ],
            'milex_message_action' => [
                'path'       => '/messages/{objectAction}/{objectId}',
                'controller' => 'MilexChannelBundle:Message:execute',
            ],
            'milex_channel_batch_contact_set' => [
                'path'       => '/channels/batch/contact/set',
                'controller' => 'MilexChannelBundle:BatchContact:set',
            ],
            'milex_channel_batch_contact_view' => [
                'path'       => '/channels/batch/contact/view',
                'controller' => 'MilexChannelBundle:BatchContact:index',
            ],
        ],
        'api' => [
            'milex_api_messagetandard' => [
                'standard_entity' => true,
                'name'            => 'messages',
                'path'            => '/messages',
                'controller'      => 'MilexChannelBundle:Api\MessageApi',
            ],
        ],
        'public' => [
        ],
    ],

    'menu' => [
        'main' => [
            'milex.channel.messages' => [
                'route'    => 'milex_message_index',
                'access'   => ['channel:messages:viewown', 'channel:messages:viewother'],
                'parent'   => 'milex.core.channels',
                'priority' => 110,
            ],
        ],
        'admin' => [
        ],
        'profile' => [
        ],
        'extra' => [
        ],
    ],

    'categories' => [
        'messages' => null,
    ],

    'services' => [
        'events' => [
            'milex.channel.campaignbundle.subscriber' => [
                'class'     => Milex\ChannelBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.channel.model.message',
                    'milex.campaign.dispatcher.action',
                    'milex.campaign.event_collector',
                    'monolog.logger.milex',
                    'translator',
                ],
            ],
            'milex.channel.channelbundle.subscriber' => [
                'class'     => \Milex\ChannelBundle\EventListener\MessageSubscriber::class,
                'arguments' => [
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.channel.channelbundle.lead.subscriber' => [
                'class'     => Milex\ChannelBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'translator',
                    'router',
                    'milex.channel.repository.message_queue',
                ],
            ],
            'milex.channel.reportbundle.subscriber' => [
                'class'     => Milex\ChannelBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'milex.lead.model.company_report_data',
                    'router',
                ],
            ],
            'milex.channel.button.subscriber' => [
                'class'     => \Milex\ChannelBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'router',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            \Milex\ChannelBundle\Form\Type\MessageType::class => [
                'class'       => \Milex\ChannelBundle\Form\Type\MessageType::class,
                'methodCalls' => [
                    'setSecurity' => ['milex.security'],
                ],
                'arguments' => [
                    'milex.channel.model.message',
                ],
            ],
            'milex.form.type.message_list' => [
                'class' => \Milex\ChannelBundle\Form\Type\MessageListType::class,
            ],
            'milex.form.type.message_send' => [
                'class'     => \Milex\ChannelBundle\Form\Type\MessageSendType::class,
                'arguments' => ['router', 'milex.channel.model.message'],
            ],
        ],
        'helpers' => [
            'milex.channel.helper.channel_list' => [
                'class'     => \Milex\ChannelBundle\Helper\ChannelListHelper::class,
                'arguments' => [
                    'event_dispatcher',
                    'translator',
                ],
                'alias' => 'channel',
            ],
        ],
        'models' => [
            'milex.channel.model.message' => [
                'class'     => \Milex\ChannelBundle\Model\MessageModel::class,
                'arguments' => [
                    'milex.channel.helper.channel_list',
                    'milex.campaign.model.campaign',
                ],
            ],
            'milex.channel.model.queue' => [
                'class'     => 'Milex\ChannelBundle\Model\MessageQueueModel',
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.channel.model.channel.action' => [
                'class'     => \Milex\ChannelBundle\Model\ChannelActionModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.model.dnc',
                    'translator',
                ],
            ],
            'milex.channel.model.frequency.action' => [
                'class'     => \Milex\ChannelBundle\Model\FrequencyActionModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.repository.frequency_rule',
                ],
            ],
        ],
        'repositories' => [
            'milex.channel.repository.message_queue' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => \Milex\ChannelBundle\Entity\MessageQueue::class,
            ],
        ],
    ],

    'parameters' => [
    ],
];
