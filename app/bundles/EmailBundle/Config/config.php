<?php

return [
    'routes' => [
        'main' => [
            'milex_email_index' => [
                'path'       => '/emails/{page}',
                'controller' => 'MilexEmailBundle:Email:index',
            ],
            'milex_email_graph_stats' => [
                'path'       => '/emails-graph-stats/{objectId}/{isVariant}/{dateFrom}/{dateTo}',
                'controller' => 'MilexEmailBundle:EmailGraphStats:view',
            ],
            'milex_email_action' => [
                'path'       => '/emails/{objectAction}/{objectId}',
                'controller' => 'MilexEmailBundle:Email:execute',
            ],
            'milex_email_contacts' => [
                'path'       => '/emails/view/{objectId}/contact/{page}',
                'controller' => 'MilexEmailBundle:Email:contacts',
            ],
        ],
        'api' => [
            'milex_api_emailstandard' => [
                'standard_entity' => true,
                'name'            => 'emails',
                'path'            => '/emails',
                'controller'      => 'MilexEmailBundle:Api\EmailApi',
            ],
            'milex_api_sendemail' => [
                'path'       => '/emails/{id}/send',
                'controller' => 'MilexEmailBundle:Api\EmailApi:send',
                'method'     => 'POST',
            ],
            'milex_api_sendcontactemail' => [
                'path'       => '/emails/{id}/contact/{leadId}/send',
                'controller' => 'MilexEmailBundle:Api\EmailApi:sendLead',
                'method'     => 'POST',
            ],
            'milex_api_reply' => [
                'path'       => '/emails/reply/{trackingHash}',
                'controller' => 'MilexEmailBundle:Api\EmailApi:reply',
                'method'     => 'POST',
            ],
        ],
        'public' => [
            'milex_plugin_tracker' => [
                'path'         => '/plugin/{integration}/tracking.gif',
                'controller'   => 'MilexEmailBundle:Public:pluginTrackingGif',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'milex_email_tracker' => [
                'path'       => '/email/{idHash}.gif',
                'controller' => 'MilexEmailBundle:Public:trackingImage',
            ],
            'milex_email_webview' => [
                'path'       => '/email/view/{idHash}',
                'controller' => 'MilexEmailBundle:Public:index',
            ],
            'milex_email_unsubscribe' => [
                'path'       => '/email/unsubscribe/{idHash}',
                'controller' => 'MilexEmailBundle:Public:unsubscribe',
            ],
            'milex_email_resubscribe' => [
                'path'       => '/email/resubscribe/{idHash}',
                'controller' => 'MilexEmailBundle:Public:resubscribe',
            ],
            'milex_mailer_transport_callback' => [
                'path'       => '/mailer/{transport}/callback',
                'controller' => 'MilexEmailBundle:Public:mailerCallback',
                'method'     => ['GET', 'POST'],
            ],
            'milex_email_preview' => [
                'path'       => '/email/preview/{objectId}',
                'controller' => 'MilexEmailBundle:Public:preview',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'milex.email.emails' => [
                    'route'    => 'milex_email_index',
                    'access'   => ['email:emails:viewown', 'email:emails:viewother'],
                    'parent'   => 'milex.core.channels',
                    'priority' => 100,
                ],
            ],
        ],
    ],
    'categories' => [
        'email' => null,
    ],
    'services' => [
        'events' => [
            'milex.email.subscriber.aggregate_stats' => [
                'class'     => \Milex\EmailBundle\EventListener\GraphAggregateStatsSubscriber::class,
                'arguments' => [
                    'milex.email.helper.stats_collection',
                ],
            ],
            'milex.email.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.email.model.email',
                    'translator',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.email.queue.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\QueueSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                ],
            ],
            'milex.email.momentum.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\MomentumSubscriber::class,
                'arguments' => [
                    'milex.transport.momentum.callback',
                    'milex.queue.service',
                    'milex.email.helper.request.storage',
                    'monolog.logger.milex',
                ],
            ],
            'milex.email.monitored.bounce.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\ProcessBounceSubscriber::class,
                'arguments' => [
                    'milex.message.processor.bounce',
                ],
            ],
            'milex.email.monitored.unsubscribe.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\ProcessUnsubscribeSubscriber::class,
                'arguments' => [
                    'milex.message.processor.unsubscribe',
                    'milex.message.processor.feedbackloop',
                ],
            ],
            'milex.email.monitored.unsubscribe.replier' => [
                'class'     => \Milex\EmailBundle\EventListener\ProcessReplySubscriber::class,
                'arguments' => [
                    'milex.message.processor.replier',
                    'milex.helper.cache_storage',
                ],
            ],
            'milex.emailbuilder.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\BuilderSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.email.model.email',
                    'milex.page.model.trackable',
                    'milex.page.model.redirect',
                    'translator',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.emailtoken.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\TokenSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.lead.helper.primary_company',
                ],
            ],
            'milex.email.generated_columns.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\GeneratedColumnSubscriber::class,
            ],
            'milex.email.campaignbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'milex.campaign.executioner.realtime',
                    'milex.email.model.send_email_to_user',
                    'translator',
                ],
            ],
            'milex.email.campaignbundle.condition_subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\CampaignConditionSubscriber::class,
                'arguments' => [
                    'milex.validator.email',
                ],
            ],
            'milex.email.formbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'milex.tracker.contact',
                ],
            ],
            'milex.email.reportbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'milex.lead.model.company_report_data',
                    'milex.email.repository.stat',
                    'milex.generated.columns.provider',
                ],
            ],
            'milex.email.leadbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.email.repository.emailReply',
                    'milex.email.repository.stat',
                    'translator',
                    'router',
                ],
            ],
            'milex.email.pointbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\PointSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.email.touser.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\EmailToUserSubscriber::class,
                'arguments' => [
                    'milex.email.model.send_email_to_user',
                ],
            ],
            'milex.email.search.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.helper.user',
                    'milex.email.model.email',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.email.webhook.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\WebhookSubscriber::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                ],
            ],
            'milex.email.configbundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\ConfigSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.email.pagebundle.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'milex.campaign.executioner.realtime',
                    'request_stack',
                ],
            ],
            'milex.email.dashboard.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'router',
                ],
            ],
            'milex.email.dashboard.best.hours.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\DashboardBestHoursSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                ],
            ],
            'milex.email.broadcast.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\BroadcastSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'doctrine.orm.entity_manager',
                    'translator',
                    'milex.lead.model.lead',
                    'milex.email.model.email',
                ],
            ],
            'milex.email.messagequeue.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                ],
            ],
            'milex.email.channel.subscriber' => [
                'class' => \Milex\EmailBundle\EventListener\ChannelSubscriber::class,
            ],
            'milex.email.stats.subscriber' => [
                'class'     => \Milex\EmailBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.email.subscriber.contact_tracker' => [
                'class'     => \Milex\EmailBundle\EventListener\TrackingSubscriber::class,
                'arguments' => [
                    'milex.email.repository.stat',
                ],
            ],
            'milex.email.subscriber.determine_winner' => [
                'class'     => \Milex\EmailBundle\EventListener\DetermineWinnerSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.email' => [
                'class'     => \Milex\EmailBundle\Form\Type\EmailType::class,
                'arguments' => [
                    'translator',
                    'doctrine.orm.entity_manager',
                    'milex.stage.model.stage',
                    'milex.helper.core_parameters',
                    'milex.helper.theme',
                ],
            ],
            'milex.form.type.email.utm_tags' => [
                'class' => \Milex\EmailBundle\Form\Type\EmailUtmTagsType::class,
            ],
            'milex.form.type.emailvariant' => [
                'class'     => \Milex\EmailBundle\Form\Type\VariantType::class,
                'arguments' => ['milex.email.model.email'],
            ],
            'milex.form.type.email_list' => [
                'class' => \Milex\EmailBundle\Form\Type\EmailListType::class,
            ],
            'milex.form.type.email_click_decision' => [
                'class' => \Milex\EmailBundle\Form\Type\EmailClickDecisionType::class,
            ],
            'milex.form.type.emailopen_list' => [
                'class' => \Milex\EmailBundle\Form\Type\EmailOpenType::class,
            ],
            'milex.form.type.emailsend_list' => [
                'class'     => \Milex\EmailBundle\Form\Type\EmailSendType::class,
                'arguments' => ['router'],
            ],
            'milex.form.type.formsubmit_sendemail_admin' => [
                'class' => \Milex\EmailBundle\Form\Type\FormSubmitActionUserEmailType::class,
            ],
            'milex.email.type.email_abtest_settings' => [
                'class' => \Milex\EmailBundle\Form\Type\AbTestPropertiesType::class,
            ],
            'milex.email.type.batch_send' => [
                'class' => \Milex\EmailBundle\Form\Type\BatchSendType::class,
            ],
            'milex.form.type.emailconfig' => [
                'class'     => \Milex\EmailBundle\Form\Type\ConfigType::class,
                'arguments' => [
                    'translator',
                    'milex.email.transport_type',
                ],
            ],
            'milex.form.type.coreconfig_monitored_mailboxes' => [
                'class'     => \Milex\EmailBundle\Form\Type\ConfigMonitoredMailboxesType::class,
                'arguments' => [
                    'milex.helper.mailbox',
                ],
            ],
            'milex.form.type.coreconfig_monitored_email' => [
                'class'     => \Milex\EmailBundle\Form\Type\ConfigMonitoredEmailType::class,
                'arguments' => 'event_dispatcher',
            ],
            'milex.form.type.email_dashboard_emails_in_time_widget' => [
                'class'     => \Milex\EmailBundle\Form\Type\DashboardEmailsInTimeWidgetType::class,
            ],
            'milex.form.type.email_dashboard_sent_email_to_contacts_widget' => [
                'class'     => \Milex\EmailBundle\Form\Type\DashboardSentEmailToContactsWidgetType::class,
            ],
            'milex.form.type.email_dashboard_most_hit_email_redirects_widget' => [
                'class'     => \Milex\EmailBundle\Form\Type\DashboardMostHitEmailRedirectsWidgetType::class,
            ],
            'milex.form.type.email_to_user' => [
                'class' => Milex\EmailBundle\Form\Type\EmailToUserType::class,
            ],
        ],
        'other' => [
            'milex.spool.delegator' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Spool\DelegatingSpool::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'swiftmailer.mailer.default.transport.real',
                ],
            ],

            // Mailers
            'milex.transport.spool' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Transport\SpoolTransport::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.eventdispatcher',
                    'milex.spool.delegator',
                ],
            ],

            'milex.transport.amazon' => [
                'class'        => \Milex\EmailBundle\Swiftmailer\Transport\AmazonTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    '%milex.mailer_amazon_region%',
                    '%milex.mailer_amazon_other_region%',
                    '%milex.mailer_port%',
                    'milex.transport.amazon.callback',
                ],
                'methodCalls' => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.amazon_api' => [
                'class'        => \Milex\EmailBundle\Swiftmailer\Transport\AmazonApiTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'milex.transport.amazon.callback',
                    'monolog.logger.milex',
                ],
                'methodCalls' => [
                    'setRegion' => [
                        '%milex.mailer_amazon_region%',
                        '%milex.mailer_amazon_other_region%',
                    ],
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.mandrill' => [
                'class'        => 'Milex\EmailBundle\Swiftmailer\Transport\MandrillTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'milex.email.model.transport_callback',
                ],
                'methodCalls'  => [
                    'setUsername'      => ['%milex.mailer_user%'],
                    'setPassword'      => ['%milex.mailer_api_key%'],
                ],
            ],
            'milex.transport.mailjet' => [
                'class'        => 'Milex\EmailBundle\Swiftmailer\Transport\MailjetTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'milex.email.model.transport_callback',
                    '%milex.mailer_mailjet_sandbox%',
                    '%milex.mailer_mailjet_sandbox_default_mail%',
                ],
                'methodCalls' => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.momentum' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Transport\MomentumTransport::class,
                'arguments' => [
                    'milex.transport.momentum.callback',
                    'milex.transport.momentum.facade',
                ],
                'tag'          => 'milex.email_transport',
                'tagArguments' => [
                    \Milex\EmailBundle\Model\TransportType::TRANSPORT_ALIAS => 'milex.email.config.mailer_transport.momentum',
                    \Milex\EmailBundle\Model\TransportType::FIELD_HOST      => true,
                    \Milex\EmailBundle\Model\TransportType::FIELD_PORT      => true,
                    \Milex\EmailBundle\Model\TransportType::FIELD_API_KEY   => true,
                ],
            ],
            'milex.transport.momentum.adapter' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Momentum\Adapter\Adapter::class,
                'arguments' => [
                    'milex.transport.momentum.sparkpost',
                ],
            ],
            'milex.transport.momentum.service.swift_message' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Momentum\Service\SwiftMessageService::class,
            ],
            'milex.transport.momentum.validator.swift_message' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Momentum\Validator\SwiftMessageValidator\SwiftMessageValidator::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.transport.momentum.callback' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Momentum\Callback\MomentumCallback::class,
                'arguments' => [
                    'milex.email.model.transport_callback',
                ],
            ],
            'milex.transport.momentum.facade' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Momentum\Facade\MomentumFacade::class,
                'arguments' => [
                    'milex.transport.momentum.adapter',
                    'milex.transport.momentum.service.swift_message',
                    'milex.transport.momentum.validator.swift_message',
                    'milex.transport.momentum.callback',
                    'monolog.logger.milex',
                ],
            ],
            'milex.transport.momentum.sparkpost' => [
                'class'     => \SparkPost\SparkPost::class,
                'factory'   => ['@milex.sparkpost.factory', 'create'],
                'arguments' => [
                    '%milex.mailer_host%',
                    '%milex.mailer_api_key%',
                    '%milex.mailer_port%',
                ],
            ],
            'milex.transport.sendgrid' => [
                'class'        => \Milex\EmailBundle\Swiftmailer\Transport\SendgridTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.sendgrid_api' => [
                'class'        => \Milex\EmailBundle\Swiftmailer\Transport\SendgridApiTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'milex.transport.sendgrid_api.facade',
                    'milex.transport.sendgrid_api.calback',
                ],
            ],
            'milex.transport.sendgrid_api.facade' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\SendGridApiFacade::class,
                'arguments' => [
                    'milex.transport.sendgrid_api.sendgrid_wrapper',
                    'milex.transport.sendgrid_api.message',
                    'milex.transport.sendgrid_api.response',
                ],
            ],
            'milex.transport.sendgrid_api.mail.base' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailBase::class,
                'arguments' => [
                    'milex.helper.plain_text_message',
                ],
            ],
            'milex.transport.sendgrid_api.mail.personalization' => [
                'class' => \Milex\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailPersonalization::class,
            ],
            'milex.transport.sendgrid_api.mail.metadata' => [
                'class' => \Milex\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailMetadata::class,
            ],
            'milex.transport.sendgrid_api.mail.attachment' => [
                'class' => \Milex\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailAttachment::class,
            ],
            'milex.transport.sendgrid_api.message' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\SendGridApiMessage::class,
                'arguments' => [
                    'milex.transport.sendgrid_api.mail.base',
                    'milex.transport.sendgrid_api.mail.personalization',
                    'milex.transport.sendgrid_api.mail.metadata',
                    'milex.transport.sendgrid_api.mail.attachment',
                ],
            ],
            'milex.transport.sendgrid_api.response' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\SendGridApiResponse::class,
                'arguments' => [
                    'monolog.logger.milex',
                ],
            ],
            'milex.transport.sendgrid_api.sendgrid_wrapper' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\SendGridWrapper::class,
                'arguments' => [
                    'milex.transport.sendgrid_api.sendgrid',
                ],
            ],
            'milex.transport.sendgrid_api.sendgrid' => [
                'class'     => \SendGrid::class,
                'arguments' => [
                    '%milex.mailer_api_key%',
                ],
            ],
            'milex.transport.sendgrid_api.calback' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\SendGrid\Callback\SendGridApiCallback::class,
                'arguments' => [
                    'milex.email.model.transport_callback',
                ],
            ],
            'milex.transport.amazon.callback' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Amazon\AmazonCallback::class,
                'arguments' => [
                    'translator',
                    'monolog.logger.milex',
                    'milex.http.client',
                    'milex.email.model.transport_callback',
                ],
            ],
            'milex.transport.elasticemail' => [
                'class'        => 'Milex\EmailBundle\Swiftmailer\Transport\ElasticemailTransport',
                'arguments'    => [
                    'translator',
                    'monolog.logger.milex',
                    'milex.email.model.transport_callback',
                ],
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.pepipost' => [
                'class'        => \Milex\EmailBundle\Swiftmailer\Transport\PepipostTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'monolog.logger.milex',
                    'milex.email.model.transport_callback',
                ],
                'methodCalls' => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.postmark' => [
                'class'        => 'Milex\EmailBundle\Swiftmailer\Transport\PostmarkTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%milex.mailer_user%'],
                    'setPassword' => ['%milex.mailer_password%'],
                ],
            ],
            'milex.transport.sparkpost' => [
                'class'        => 'Milex\EmailBundle\Swiftmailer\Transport\SparkpostTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    '%milex.mailer_api_key%',
                    'translator',
                    'milex.email.model.transport_callback',
                    'milex.sparkpost.factory',
                    'monolog.logger.milex',
                ],
            ],
            'milex.sparkpost.factory' => [
                'class'     => \Milex\EmailBundle\Swiftmailer\Sparkpost\SparkpostFactory::class,
                'arguments' => [
                    'milex.guzzle.client',
                ],
            ],
            'milex.guzzle.client.factory' => [
                'class' => \Milex\EmailBundle\Swiftmailer\Guzzle\ClientFactory::class,
            ],
            /**
             * Needed for Sparkpost integration. Can be removed when this integration is moved to
             * its own plugin.
             */
            'milex.guzzle.client' => [
                'class'     => \Http\Adapter\Guzzle7\Client::class,
                'factory'   => ['@milex.guzzle.client.factory', 'create'],
            ],
            'milex.helper.mailbox' => [
                'class'     => 'Milex\EmailBundle\MonitoredEmail\Mailbox',
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.helper.paths',
                ],
            ],
            'milex.message.search.contact' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Search\ContactFinder::class,
                'arguments' => [
                    'milex.email.repository.stat',
                    'milex.lead.repository.lead',
                    'monolog.logger.milex',
                ],
            ],
            'milex.message.processor.bounce' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Processor\Bounce::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.real',
                    'milex.message.search.contact',
                    'milex.email.repository.stat',
                    'milex.lead.model.lead',
                    'translator',
                    'monolog.logger.milex',
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.message.processor.unsubscribe' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscribe::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.real',
                    'milex.message.search.contact',
                    'translator',
                    'monolog.logger.milex',
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.message.processor.feedbackloop' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Processor\FeedbackLoop::class,
                'arguments' => [
                    'milex.message.search.contact',
                    'translator',
                    'monolog.logger.milex',
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.message.processor.replier' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Processor\Reply::class,
                'arguments' => [
                    'milex.email.repository.stat',
                    'milex.message.search.contact',
                    'milex.lead.model.lead',
                    'event_dispatcher',
                    'monolog.logger.milex',
                    'milex.tracker.contact',
                ],
            ],
            'milex.helper.mailer' => [
                'class'     => \Milex\EmailBundle\Helper\MailHelper::class,
                'arguments' => [
                    'milex.factory',
                    'mailer',
                ],
            ],
            'milex.helper.plain_text_message' => [
                'class'     => \Milex\EmailBundle\Helper\PlainTextMessageHelper::class,
            ],
            'milex.validator.email' => [
                'class'     => \Milex\EmailBundle\Helper\EmailValidator::class,
                'arguments' => [
                    'translator',
                    'event_dispatcher',
                ],
            ],
            'milex.email.fetcher' => [
                'class'     => \Milex\EmailBundle\MonitoredEmail\Fetcher::class,
                'arguments' => [
                    'milex.helper.mailbox',
                    'event_dispatcher',
                    'translator',
                ],
            ],
            'milex.email.helper.stat' => [
                'class'     => \Milex\EmailBundle\Stat\StatHelper::class,
                'arguments' => [
                    'milex.email.repository.stat',
                ],
            ],
            'milex.email.helper.request.storage' => [
                'class'     => \Milex\EmailBundle\Helper\RequestStorageHelper::class,
                'arguments' => [
                    'milex.helper.cache_storage',
                ],
            ],
            'milex.email.helper.stats_collection' => [
                'class'     => \Milex\EmailBundle\Helper\StatsCollectionHelper::class,
                'arguments' => [
                    'milex.email.stats.helper_container',
                ],
            ],
            'milex.email.stats.helper_container' => [
                'class' => \Milex\EmailBundle\Stats\StatHelperContainer::class,
            ],
            'milex.email.stats.helper_bounced' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\BouncedHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
            'milex.email.stats.helper_clicked' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\ClickedHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
            'milex.email.stats.helper_failed' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\FailedHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
            'milex.email.stats.helper_opened' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\OpenedHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
            'milex.email.stats.helper_sent' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\SentHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
            'milex.email.stats.helper_unsubscribed' => [
                'class'     => \Milex\EmailBundle\Stats\Helper\UnsubscribedHelper::class,
                'arguments' => [
                    'milex.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'milex.generated.columns.provider',
                    'milex.helper.user',
                ],
                'tag' => 'milex.email_stat_helper',
            ],
        ],
        'models' => [
            'milex.email.model.email' => [
                'class'     => \Milex\EmailBundle\Model\EmailModel::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.helper.theme',
                    'milex.helper.mailbox',
                    'milex.helper.mailer',
                    'milex.lead.model.lead',
                    'milex.lead.model.company',
                    'milex.page.model.trackable',
                    'milex.user.model.user',
                    'milex.channel.model.queue',
                    'milex.email.model.send_email_to_contacts',
                    'milex.tracker.device',
                    'milex.page.repository.redirect',
                    'milex.helper.cache_storage',
                    'milex.tracker.contact',
                    'milex.lead.model.dnc',
                    'milex.email.helper.stats_collection',
                    'milex.security',
                    'doctrine.dbal.default_connection',
                ],
            ],
            'milex.email.model.send_email_to_user' => [
                'class'     => \Milex\EmailBundle\Model\SendEmailToUser::class,
                'arguments' => [
                    'milex.email.model.email',
                    'event_dispatcher',
                    'milex.lead.validator.custom_field',
                    'milex.validator.email',
                ],
            ],
            'milex.email.model.send_email_to_contacts' => [
                'class'     => \Milex\EmailBundle\Model\SendEmailToContact::class,
                'arguments' => [
                    'milex.helper.mailer',
                    'milex.email.helper.stat',
                    'milex.lead.model.dnc',
                    'translator',
                ],
            ],
            'milex.email.model.transport_callback' => [
                'class'     => \Milex\EmailBundle\Model\TransportCallback::class,
                'arguments' => [
                    'milex.lead.model.dnc',
                    'milex.message.search.contact',
                    'milex.email.repository.stat',
                ],
            ],
            'milex.email.transport_type' => [
                'class'     => \Milex\EmailBundle\Model\TransportType::class,
                'arguments' => [],
            ],
        ],
        'commands' => [
            'milex.email.command.fetch' => [
                'class'     => \Milex\EmailBundle\Command\ProcessFetchEmailCommand::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.email.fetcher',
                ],
                'tag' => 'console.command',
            ],
            'milex.email.command.queue' => [
                'class'     => \Milex\EmailBundle\Command\ProcessEmailQueueCommand::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.real',
                    'event_dispatcher',
                    'milex.helper.core_parameters',
                ],
                'tag' => 'console.command',
            ],
        ],
        'validator' => [
            'milex.email.validator.multiple_emails_valid_validator' => [
                'class'     => \Milex\EmailBundle\Validator\MultipleEmailsValidValidator::class,
                'arguments' => [
                    'milex.validator.email',
                ],
                'tag' => 'validator.constraint_validator',
            ],
            'milex.email.validator.email_or_token_list_validator' => [
                'class'     => \Milex\EmailBundle\Validator\EmailOrEmailTokenListValidator::class,
                'arguments' => [
                    'milex.validator.email',
                    'milex.lead.validator.custom_field',
                ],
                'tag' => 'validator.constraint_validator',
            ],
        ],
        'repositories' => [
            'milex.email.repository.email' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\EmailBundle\Entity\Email::class,
                ],
            ],
            'milex.email.repository.emailReply' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\EmailBundle\Entity\EmailReply::class,
                ],
            ],
            'milex.email.repository.stat' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\EmailBundle\Entity\Stat::class,
                ],
            ],
        ],
        'fixtures' => [
            'milex.email.fixture.email' => [
                'class'     => Milex\EmailBundle\DataFixtures\ORM\LoadEmailData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.email.model.email'],
            ],
        ],
    ],
    'parameters' => [
        'mailer_api_key'                 => null, // Api key from mail delivery provider.
        'mailer_from_name'               => 'Milex',
        'mailer_from_email'              => 'email@yoursite.com',
        'mailer_reply_to_email'          => null,
        'mailer_return_path'             => null,
        'mailer_transport'               => 'smtp',
        'mailer_append_tracking_pixel'   => true,
        'mailer_convert_embed_images'    => false,
        'mailer_host'                    => '',
        'mailer_port'                    => null,
        'mailer_user'                    => null,
        'mailer_password'                => null,
        'mailer_encryption'              => null, //tls or ssl,
        'mailer_auth_mode'               => null, //plain, login or cram-md5
        'mailer_amazon_region'           => 'us-east-1',
        'mailer_amazon_other_region'     => null,
        'mailer_custom_headers'          => [],
        'mailer_spool_type'              => 'memory', //memory = immediate; file = queue
        'mailer_spool_path'              => '%kernel.root_dir%/../var/spool',
        'mailer_spool_msg_limit'         => null,
        'mailer_spool_time_limit'        => null,
        'mailer_spool_recover_timeout'   => 900,
        'mailer_spool_clear_timeout'     => 1800,
        'unsubscribe_text'               => null,
        'webview_text'                   => null,
        'unsubscribe_message'            => null,
        'resubscribe_message'            => null,
        'monitored_email'                => [
            'general' => [
                'address'         => null,
                'host'            => null,
                'port'            => '993',
                'encryption'      => '/ssl',
                'user'            => null,
                'password'        => null,
                'use_attachments' => false,
            ],
            'EmailBundle_bounces' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_unsubscribes' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_replies' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
        ],
        'mailer_is_owner'                     => false,
        'default_signature_text'              => null,
        'email_frequency_number'              => 0,
        'email_frequency_time'                => 'DAY',
        'show_contact_preferences'            => false,
        'show_contact_frequency'              => false,
        'show_contact_pause_dates'            => false,
        'show_contact_preferred_channels'     => false,
        'show_contact_categories'             => false,
        'show_contact_segments'               => false,
        'mailer_mailjet_sandbox'              => false,
        'mailer_mailjet_sandbox_default_mail' => null,
        'disable_trackable_urls'              => false,
        'theme_email_default'                 => 'blank',
    ],
];
