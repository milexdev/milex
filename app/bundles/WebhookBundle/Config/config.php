<?php

return [
    'routes' => [
        'main' => [
            'milex_webhook_index' => [
                'path'       => '/webhooks/{page}',
                'controller' => 'MilexWebhookBundle:Webhook:index',
            ],
            'milex_webhook_action' => [
                'path'       => '/webhooks/{objectAction}/{objectId}',
                'controller' => 'MilexWebhookBundle:Webhook:execute',
            ],
        ],
        'api' => [
            'milex_api_webhookstandard' => [
                'standard_entity' => true,
                'name'            => 'hooks',
                'path'            => '/hooks',
                'controller'      => 'MilexWebhookBundle:Api\WebhookApi',
            ],
            'milex_api_webhookevents' => [
                'path'       => '/hooks/triggers',
                'controller' => 'MilexWebhookBundle:Api\WebhookApi:getTriggers',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'items' => [
                'milex.webhook.webhooks' => [
                    'id'        => 'milex_webhook_root',
                    'iconClass' => 'fa-exchange',
                    'access'    => ['webhook:webhooks:viewown', 'webhook:webhooks:viewother'],
                    'route'     => 'milex_webhook_index',
                ],
            ],
        ],
    ],

    'services' => [
        'forms' => [
            'milex.form.type.webhook' => [
                'class'     => \Milex\WebhookBundle\Form\Type\WebhookType::class,
            ],
            'milex.form.type.webhookconfig' => [
                'class' => \Milex\WebhookBundle\Form\Type\ConfigType::class,
            ],
            'milex.campaign.type.action.sendwebhook' => [
                'class'     => \Milex\WebhookBundle\Form\Type\CampaignEventSendWebhookType::class,
                'arguments' => [
                    'arguments' => 'translator',
                ],
            ],
            'milex.webhook.notificator.webhookkillnotificator' => [
                'class'     => \Milex\WebhookBundle\Notificator\WebhookKillNotificator::class,
                'arguments' => [
                    'translator',
                    'router',
                    'milex.core.model.notification',
                    'doctrine.orm.entity_manager',
                    'milex.helper.mailer',
                    'milex.helper.core_parameters',
                ],
            ],
        ],
        'events' => [
            'milex.webhook.config.subscriber' => [
                'class' => \Milex\WebhookBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.webhook.audit.subscriber' => [
                'class'     => \Milex\WebhookBundle\EventListener\WebhookSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.webhook.notificator.webhookkillnotificator',
                ],
            ],
            'milex.webhook.stats.subscriber' => [
                'class'     => \Milex\WebhookBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.webhook.campaign.subscriber' => [
                'class'     => \Milex\WebhookBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.webhook.campaign.helper',
                ],
            ],
        ],
        'models' => [
            'milex.webhook.model.webhook' => [
                'class'     => \Milex\WebhookBundle\Model\WebhookModel::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'jms_serializer',
                    'milex.webhook.http.client',
                    'event_dispatcher',
                ],
            ],
        ],
        'others' => [
            'milex.webhook.campaign.helper' => [
                'class'     => \Milex\WebhookBundle\Helper\CampaignHelper::class,
                'arguments' => [
                    'milex.http.client',
                    'milex.lead.model.company',
                    'event_dispatcher',
                ],
            ],
            'milex.webhook.http.client' => [
                'class'     => \Milex\WebhookBundle\Http\Client::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.guzzle.client',
                ],
            ],
        ],
        'commands' => [
            'milex.webhook.command.process.queues' => [
                'class'     => \Milex\WebhookBundle\Command\ProcessWebhookQueuesCommand::class,
                'tag'       => 'console.command',
            ],
            'milex.webhook.command.delete.logs' => [
                'class'     => \Milex\WebhookBundle\Command\DeleteWebhookLogsCommand::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                    'milex.helper.core_parameters',
                ],
                'tag' => 'console.command',
            ],
        ],
        'repositories' => [
            'milex.webhook.repository.queue' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\WebhookBundle\Entity\WebhookQueue::class,
                ],
            ],
        ],
    ],

    'parameters' => [
        'webhook_limit'                        => 10, // How many entities can be sent in one webhook
        'webhook_time_limit'                   => 600, // How long the webhook processing can run in seconds
        'webhook_log_max'                      => 1000, // How many recent logs to keep
        'clean_webhook_logs_in_background'     => false,
        'webhook_disable_limit'                => 100, // How many times the webhook response can fail until the webhook will be unpublished
        'webhook_timeout'                      => 15, // How long the CURL request can wait for response before Milex hangs up. In seconds
        'queue_mode'                           => \Milex\WebhookBundle\Model\WebhookModel::IMMEDIATE_PROCESS, // Trigger the webhook immediately or queue it for faster response times
        'events_orderby_dir'                   => \Doctrine\Common\Collections\Criteria::ASC, // Order the queued events chronologically or the other way around
        'webhook_email_details'                => true, // If enabled, email related webhooks send detailed data
    ],
];
