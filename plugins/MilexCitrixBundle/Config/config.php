<?php

return [
    'name'        => 'Citrix',
    'description' => 'Enables integration with Milex supported Citrix collaboration products.',
    'version'     => '1.0',
    'author'      => 'Milex',
    'routes'      => [
        'public' => [
            'milex_citrix_proxy' => [
                'path'       => '/citrix/proxy',
                'controller' => 'MilexCitrixBundle:Public:proxy',
            ],
            'milex_citrix_sessionchanged' => [
                'path'       => '/citrix/sessionChanged',
                'controller' => 'MilexCitrixBundle:Public:sessionChanged',
            ],
        ],
    ],
    'services' => [
        'events' => [
            'milex.citrix.formbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'milex.citrix.model.citrix',
                    'milex.form.model.form',
                    'milex.form.model.submission',
                    'translator',
                    'doctrine.orm.entity_manager',
                    'milex.helper.templating',
                ],
                'methodCalls' => [
                    'setEmailModel' => ['milex.email.model.email'],
                ],
            ],
            'milex.citrix.leadbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.citrix.model.citrix',
                    'translator',
                ],
            ],
            'milex.citrix.campaignbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.citrix.model.citrix',
                    'translator',
                    'milex.helper.templating',
                ],
                'methodCalls' => [
                    'setEmailModel' => ['milex.email.model.email'],
                ],
            ],
            'milex.citrix.emailbundle.subscriber' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'milex.citrix.model.citrix',
                    'translator',
                    'event_dispatcher',
                    'milex.helper.templating',
                ],
            ],
            'milex.citrix.stats.subscriber' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.citrix.integration.request' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\EventListener\IntegrationRequestSubscriber::class,
            ],
        ],
        'forms' => [
            'milex.form.type.fieldslist.citrixlist' => [
                'class' => \MilexPlugin\MilexCitrixBundle\Form\Type\CitrixListType::class,
            ],
            'milex.form.type.citrix.submitaction' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Form\Type\CitrixActionType::class,
                'arguments' => [
                    'milex.form.model.field',
                ],
            ],
            'milex.form.type.citrix.campaignevent' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Form\Type\CitrixCampaignEventType::class,
                'arguments' => [
                    'milex.citrix.model.citrix',
                    'translator',
                ],
            ],
            'milex.form.type.citrix.campaignaction' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Form\Type\CitrixCampaignActionType::class,
                'arguments' => [
                    'translator',
                ],
            ],
        ],
        'models' => [
            'milex.citrix.model.citrix' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Model\CitrixModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.campaign.model.event',
                ],
            ],
        ],
        'fixtures' => [
            'milex.citrix.fixture.load_citrix_data' => [
                'class'     => MilexPlugin\MilexCitrixBundle\Tests\DataFixtures\ORM\LoadCitrixData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['doctrine.orm.entity_manager'],
                'optional'  => true,
            ],
        ],
        'integrations' => [
            'milex.integration.gotoassist' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Integration\GotoassistIntegration::class,
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
            'milex.integration.gotomeeting' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Integration\GotomeetingIntegration::class,
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
            'milex.integration.gototraining' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Integration\GototrainingIntegration::class,
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
            'milex.integration.gotowebinar' => [
                'class'     => \MilexPlugin\MilexCitrixBundle\Integration\GotowebinarIntegration::class,
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
    ],
];
