<?php

return [
    'services' => [
        'events' => [
            'milex.sms.lead.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'translator',
                    'router',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.sms.broadcast.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\BroadcastSubscriber::class,
                'arguments' => [
                    'milex.sms.broadcast.executioner',
                ],
            ],
            'milex.sms.campaignbundle.subscriber.send' => [
                'class'     => \Milex\SmsBundle\EventListener\CampaignSendSubscriber::class,
                'arguments' => [
                    'milex.sms.model.sms',
                    'milex.sms.transport_chain',
                ],
                'alias' => 'milex.sms.campaignbundle.subscriber',
            ],
            'milex.sms.campaignbundle.subscriber.reply' => [
                'class'     => \Milex\SmsBundle\EventListener\CampaignReplySubscriber::class,
                'arguments' => [
                    'milex.sms.transport_chain',
                    'milex.campaign.executioner.realtime',
                ],
            ],
            'milex.sms.smsbundle.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\SmsSubscriber::class,
                'arguments' => [
                    'milex.core.model.auditlog',
                    'milex.page.model.trackable',
                    'milex.page.helper.token',
                    'milex.asset.helper.token',
                    'milex.helper.sms',
                ],
            ],
            'milex.sms.channel.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'milex.sms.transport_chain',
                ],
            ],
            'milex.sms.message_queue.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'milex.sms.model.sms',
                ],
            ],
            'milex.sms.stats.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.sms.configbundle.subscriber' => [
                'class' => Milex\SmsBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.sms.subscriber.contact_tracker' => [
                'class'     => \Milex\SmsBundle\EventListener\TrackingSubscriber::class,
                'arguments' => [
                    'milex.sms.repository.stat',
                ],
            ],
            'milex.sms.subscriber.stop' => [
                'class'     => \Milex\SmsBundle\EventListener\StopSubscriber::class,
                'arguments' => [
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.sms.subscriber.reply' => [
                'class'     => \Milex\SmsBundle\EventListener\ReplySubscriber::class,
                'arguments' => [
                    'translator',
                    'milex.lead.repository.lead_event_log',
                ],
            ],
            'milex.sms.webhook.subscriber' => [
                'class'     => \Milex\SmsBundle\EventListener\WebhookSubscriber::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.sms' => [
                'class'     => \Milex\SmsBundle\Form\Type\SmsType::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.form.type.smsconfig' => [
                'class' => \Milex\SmsBundle\Form\Type\ConfigType::class,
            ],
            'milex.form.type.smssend_list' => [
                'class'     => \Milex\SmsBundle\Form\Type\SmsSendType::class,
                'arguments' => 'router',
            ],
            'milex.form.type.sms_list' => [
                'class' => \Milex\SmsBundle\Form\Type\SmsListType::class,
            ],
            'milex.form.type.sms.config.form' => [
                'class'     => \Milex\SmsBundle\Form\Type\ConfigType::class,
                'arguments' => ['milex.sms.transport_chain', 'translator'],
            ],
            'milex.form.type.sms.campaign_reply_type' => [
                'class' => \Milex\SmsBundle\Form\Type\CampaignReplyType::class,
            ],
        ],
        'helpers' => [
            'milex.helper.sms' => [
                'class'     => \Milex\SmsBundle\Helper\SmsHelper::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.lead.model.lead',
                    'milex.helper.phone_number',
                    'milex.sms.model.sms',
                    'milex.helper.integration',
                    'milex.lead.model.dnc',
                ],
                'alias' => 'sms_helper',
            ],
        ],
        'other' => [
            'milex.sms.transport_chain' => [
                'class'     => \Milex\SmsBundle\Sms\TransportChain::class,
                'arguments' => [
                    '%milex.sms_transport%',
                    'milex.helper.integration',
                    'monolog.logger.milex',
                ],
            ],
            'milex.sms.callback_handler_container' => [
                'class' => \Milex\SmsBundle\Callback\HandlerContainer::class,
            ],
            'milex.sms.helper.contact' => [
                'class'     => \Milex\SmsBundle\Helper\ContactHelper::class,
                'arguments' => [
                    'milex.lead.repository.lead',
                    'doctrine.dbal.default_connection',
                    'milex.helper.phone_number',
                ],
            ],
            'milex.sms.helper.reply' => [
                'class'     => \Milex\SmsBundle\Helper\ReplyHelper::class,
                'arguments' => [
                    'event_dispatcher',
                    'monolog.logger.milex',
                    'milex.tracker.contact',
                ],
            ],
            'milex.sms.twilio.configuration' => [
                'class'        => \Milex\SmsBundle\Integration\Twilio\Configuration::class,
                'arguments'    => [
                    'milex.helper.integration',
                ],
            ],
            'milex.sms.twilio.transport' => [
                'class'        => \Milex\SmsBundle\Integration\Twilio\TwilioTransport::class,
                'arguments'    => [
                    'milex.sms.twilio.configuration',
                    'monolog.logger.milex',
                ],
                'tag'          => 'milex.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'Twilio',
                ],
                'serviceAliases' => [
                    'sms_api',
                    'milex.sms.api',
                ],
            ],
            'milex.sms.twilio.callback' => [
                'class'     => \Milex\SmsBundle\Integration\Twilio\TwilioCallback::class,
                'arguments' => [
                    'milex.sms.helper.contact',
                    'milex.sms.twilio.configuration',
                ],
                'tag'   => 'milex.sms_callback_handler',
            ],

            // @deprecated - this should not be used; use `milex.sms.twilio.transport` instead.
            // Only kept as BC in case someone is passing the service by name in 3rd party
            'milex.sms.transport.twilio' => [
                'class'        => \Milex\SmsBundle\Api\TwilioApi::class,
                'arguments'    => [
                    'milex.sms.twilio.configuration',
                    'monolog.logger.milex',
                ],
            ],
            'milex.sms.broadcast.executioner' => [
                'class'        => \Milex\SmsBundle\Broadcast\BroadcastExecutioner::class,
                'arguments'    => [
                    'milex.sms.model.sms',
                    'milex.sms.broadcast.query',
                    'translator',
                ],
            ],
            'milex.sms.broadcast.query' => [
                'class'        => \Milex\SmsBundle\Broadcast\BroadcastQuery::class,
                'arguments'    => [
                    'doctrine.orm.entity_manager',
                    'milex.sms.model.sms',
                ],
            ],
        ],
        'models' => [
            'milex.sms.model.sms' => [
                'class'     => 'Milex\SmsBundle\Model\SmsModel',
                'arguments' => [
                    'milex.page.model.trackable',
                    'milex.lead.model.lead',
                    'milex.channel.model.queue',
                    'milex.sms.transport_chain',
                    'milex.helper.cache_storage',
                ],
            ],
        ],
        'integrations' => [
            'milex.integration.twilio' => [
                'class'     => \Milex\SmsBundle\Integration\TwilioIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'milex.helper.encryption',
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                    'milex.helper.paths',
                    'milex.core.model.notification',
                    'milex.lead.model.field',
                    'milex.plugin.model.integration_entity',
                    'milex.lead.model.dnc',
                ],
            ],
        ],
        'repositories' => [
            'milex.sms.repository.stat' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\SmsBundle\Entity\Stat::class,
                ],
            ],
        ],
        'controllers' => [
            'milex.sms.controller.reply' => [
                'class'     => \Milex\SmsBundle\Controller\ReplyController::class,
                'arguments' => [
                    'milex.sms.callback_handler_container',
                    'milex.sms.helper.reply',
                ],
                'methodCalls' => [
                    'setContainer' => [
                        '@service_container',
                    ],
                ],
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'milex_sms_index' => [
                'path'       => '/sms/{page}',
                'controller' => 'MilexSmsBundle:Sms:index',
            ],
            'milex_sms_action' => [
                'path'       => '/sms/{objectAction}/{objectId}',
                'controller' => 'MilexSmsBundle:Sms:execute',
            ],
            'milex_sms_contacts' => [
                'path'       => '/sms/view/{objectId}/contact/{page}',
                'controller' => 'MilexSmsBundle:Sms:contacts',
            ],
        ],
        'public' => [
            'milex_sms_callback' => [
                'path'       => '/sms/{transport}/callback',
                'controller' => 'MilexSmsBundle:Reply:callback',
            ],
            /* @deprecated as this was Twilio specific */
            'milex_receive_sms' => [
                'path'       => '/sms/receive',
                'controller' => 'MilexSmsBundle:Reply:callback',
                'defaults'   => [
                    'transport' => 'twilio',
                ],
            ],
        ],
        'api' => [
            'milex_api_smsesstandard' => [
                'standard_entity' => true,
                'name'            => 'smses',
                'path'            => '/smses',
                'controller'      => 'MilexSmsBundle:Api\SmsApi',
            ],
            'milex_api_smses_send' => [
                'path'       => '/smses/{id}/contact/{contactId}/send',
                'controller' => 'MilexSmsBundle:Api\SmsApi:send',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'milex.sms.smses' => [
                    'route'  => 'milex_sms_index',
                    'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent' => 'milex.core.channels',
                    'checks' => [
                        'integration' => [
                            'Twilio' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'parameters' => [
        'sms_enabled'              => false,
        'sms_username'             => null,
        'sms_password'             => null,
        'sms_sending_phone_number' => null,
        'sms_frequency_number'     => 0,
        'sms_frequency_time'       => 'DAY',
        'sms_transport'            => 'milex.sms.twilio.transport',
    ],
];
