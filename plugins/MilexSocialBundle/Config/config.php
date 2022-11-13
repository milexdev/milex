<?php

return [
    'name'        => 'Social Media',
    'description' => 'Enables integrations with Milex supported social media services.',
    'version'     => '1.0',
    'author'      => 'Milex',

    'routes' => [
        'main' => [
            'milex_social_index' => [
                'path'       => '/monitoring/{page}',
                'controller' => 'MilexSocialBundle:Monitoring:index',
            ],
            'milex_social_action' => [
                'path'       => '/monitoring/{objectAction}/{objectId}',
                'controller' => 'MilexSocialBundle:Monitoring:execute',
            ],
            'milex_social_contacts' => [
                'path'       => '/monitoring/view/{objectId}/contacts/{page}',
                'controller' => 'MilexSocialBundle:Monitoring:contacts',
            ],
            'milex_tweet_index' => [
                'path'       => '/tweets/{page}',
                'controller' => 'MilexSocialBundle:Tweet:index',
            ],
            'milex_tweet_action' => [
                'path'       => '/tweets/{objectAction}/{objectId}',
                'controller' => 'MilexSocialBundle:Tweet:execute',
            ],
        ],
        'api' => [
            'milex_api_tweetsstandard' => [
                'standard_entity' => true,
                'name'            => 'tweets',
                'path'            => '/tweets',
                'controller'      => 'MilexSocialBundle:Api\TweetApi',
            ],
        ],
        'public' => [
            'milex_social_js_generate' => [
                'path'       => '/social/generate/{formName}.js',
                'controller' => 'MilexSocialBundle:Js:generate',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.social.formbundle.subscriber' => [
                'class' => \MilexPlugin\MilexSocialBundle\EventListener\FormSubscriber::class,
            ],
            'milex.social.campaignbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexSocialBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.social.helper.campaign',
                    'milex.helper.integration',
                    'translator',
                ],
            ],
            'milex.social.configbundle.subscriber' => [
                'class' => \MilexPlugin\MilexSocialBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.social.subscriber.channel' => [
                'class'     => \MilexPlugin\MilexSocialBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'milex.helper.integration',
                ],
            ],
            'milex.social.stats.subscriber' => [
                'class'     => \MilexPlugin\MilexSocialBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.social.sociallogin' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Form\Type\SocialLoginType',
                'arguments' => [
                    'milex.helper.integration',
                    'milex.form.model.form',
                    'milex.helper.core_parameters',
                    ],
            ],
            'milex.form.type.social.facebook' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\FacebookType',
            ],
            'milex.form.type.social.twitter' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TwitterType',
            ],
            'milex.form.type.social.linkedin' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\LinkedInType',
            ],
            'milex.social.form.type.twitter.tweet' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Form\Type\TweetType',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.social.form.type.monitoring' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Form\Type\MonitoringType',
                'arguments' => [
                    'milex.social.model.monitoring',
                ],
            ],
            'milex.social.form.type.network.twitter.abstract' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TwitterAbstractType',
            ],
            'milex.social.form.type.network.twitter.hashtag' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TwitterHashtagType',
            ],
            'milex.social.form.type.network.twitter.mention' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TwitterMentionType',
            ],
            'milex.social.form.type.network.twitter.custom' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TwitterCustomType',
            ],
            'milex.social.config' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Form\Type\ConfigType',
                'arguments' => 'milex.lead.model.field',
            ],
            'milex.social.tweet.list' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Form\Type\TweetListType',
            ],
            'milex.social.tweetsend_list' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Form\Type\TweetSendType',
                'arguments' => 'router',
            ],
        ],
        'models' => [
            'milex.social.model.monitoring' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Model\MonitoringModel',
            ],
            'milex.social.model.postcount' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Model\PostCountModel',
            ],
            'milex.social.model.tweet' => [
                'class' => 'MilexPlugin\MilexSocialBundle\Model\TweetModel',
            ],
        ],
        'others' => [
            'milex.social.helper.campaign' => [
                'class'     => 'MilexPlugin\MilexSocialBundle\Helper\CampaignEventHelper',
                'arguments' => [
                    'milex.helper.integration',
                    'milex.page.model.trackable',
                    'milex.page.helper.token',
                    'milex.asset.helper.token',
                    'milex.social.model.tweet',
                ],
            ],
            'milex.social.helper.twitter_command' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Helper\TwitterCommandHelper::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.model.field',
                    'milex.social.model.monitoring',
                    'milex.social.model.postcount',
                    'translator',
                    'doctrine.orm.entity_manager',
                    'milex.helper.core_parameters',
                ],
            ],
        ],
        'integrations' => [
            'milex.integration.facebook' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Integration\FacebookIntegration::class,
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
                    'milex.helper.integration',
                ],
            ],
            'milex.integration.foursquare' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Integration\FoursquareIntegration::class,
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
                    'milex.helper.integration',
                ],
            ],
            'milex.integration.instagram' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Integration\InstagramIntegration::class,
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
                    'milex.helper.integration',
                ],
            ],
            'milex.integration.linkedin' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Integration\LinkedInIntegration::class,
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
                    'milex.helper.integration',
                ],
            ],
            'milex.integration.twitter' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Integration\TwitterIntegration::class,
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
                    'milex.helper.integration',
                ],
            ],
        ],
        'command' => [
            'milex.social.command.twitter_hashtags' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Command\MonitorTwitterHashtagsCommand::class,
                'arguments' => [
                    'event_dispatcher',
                    'translator',
                    'milex.helper.integration',
                    'milex.social.helper.twitter_command',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.social.command.twitter_mentions' => [
                'class'     => \MilexPlugin\MilexSocialBundle\Command\MonitorTwitterMentionsCommand::class,
                'arguments' => [
                    'event_dispatcher',
                    'translator',
                    'milex.helper.integration',
                    'milex.social.helper.twitter_command',
                    'milex.helper.core_parameters',
                ],
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'milex.social.monitoring' => [
                'route'    => 'milex_social_index',
                'parent'   => 'milex.core.channels',
                'access'   => 'milexSocial:monitoring:view',
                'priority' => 0,
            ],
            'milex.social.tweets' => [
                'route'    => 'milex_tweet_index',
                'access'   => ['milexSocial:tweets:viewown', 'milexSocial:tweets:viewother'],
                'parent'   => 'milex.core.channels',
                'priority' => 80,
                'checks'   => [
                    'integration' => [
                        'Twitter' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'categories' => [
        'plugin:milexSocial' => 'milex.social.monitoring',
    ],

    'twitter' => [
        'tweet_request_count' => 100,
    ],

    'parameters' => [
        'twitter_handle_field' => 'twitter',
    ],
];
