<?php

return [
    'routes' => [
        'public' => [
            'milex_installer_home' => [
                'path'       => '/installer',
                'controller' => 'MilexInstallBundle:Install:step',
            ],
            'milex_installer_remove_slash' => [
                'path'       => '/installer/',
                'controller' => 'MilexCoreBundle:Common:removeTrailingSlash',
            ],
            'milex_installer_step' => [
                'path'       => '/installer/step/{index}',
                'controller' => 'MilexInstallBundle:Install:step',
            ],
            'milex_installer_final' => [
                'path'       => '/installer/final',
                'controller' => 'MilexInstallBundle:Install:final',
            ],
            'milex_installer_catchcall' => [
                'path'         => '/installer/{noerror}',
                'controller'   => 'MilexInstallBundle:Install:step',
                'requirements' => [
                    'noerror' => '^(?).+',
                ],
            ],
        ],
    ],

    'services' => [
        'fixtures' => [
            'milex.install.fixture.lead_field' => [
                'class'     => \Milex\InstallBundle\InstallFixtures\ORM\LeadFieldData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => [],
            ],
            'milex.install.fixture.role' => [
                'class'     => \Milex\InstallBundle\InstallFixtures\ORM\RoleData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => [],
            ],
            'milex.install.fixture.report_data' => [
                'class'     => \Milex\InstallBundle\InstallFixtures\ORM\LoadReportData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => [],
            ],
            'milex.install.fixture.grape_js' => [
                'class'     => \Milex\InstallBundle\InstallFixtures\ORM\GrapesJsData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => [],
            ],
        ],
        'forms' => [
            \Milex\InstallBundle\Configurator\Form\CheckStepType::class => [
                'class' => \Milex\InstallBundle\Configurator\Form\CheckStepType::class,
            ],
            \Milex\InstallBundle\Configurator\Form\DoctrineStepType::class => [
                'class' => \Milex\InstallBundle\Configurator\Form\DoctrineStepType::class,
            ],
            \Milex\InstallBundle\Configurator\Form\EmailStepType::class => [
                'class'     => \Milex\InstallBundle\Configurator\Form\EmailStepType::class,
                'arguments' => [
                    'translator',
                    'milex.email.transport_type',
                ],
            ],
            \Milex\InstallBundle\Configurator\Form\UserStepType::class => [
                'class'     => \Milex\InstallBundle\Configurator\Form\UserStepType::class,
                'arguments' => ['session'],
            ],
        ],
        'other' => [
            'milex.install.configurator.step.check' => [
                'class'     => \Milex\InstallBundle\Configurator\Step\CheckStep::class,
                'arguments' => [
                    'milex.configurator',
                    '%kernel.root_dir%',
                    'request_stack',
                    'milex.cipher.openssl',
                ],
                'tag'          => 'milex.configurator.step',
                'tagArguments' => [
                    'priority' => 0,
                ],
            ],
            'milex.install.configurator.step.doctrine' => [
                'class'     => \Milex\InstallBundle\Configurator\Step\DoctrineStep::class,
                'arguments' => [
                    'milex.configurator',
                ],
                'tag'          => 'milex.configurator.step',
                'tagArguments' => [
                    'priority' => 1,
                ],
            ],
            'milex.install.configurator.step.email' => [
                'class'     => \Milex\InstallBundle\Configurator\Step\EmailStep::class,
                'arguments' => [
                    'session',
                ],
                'tag'          => 'milex.configurator.step',
                'tagArguments' => [
                    'priority' => 3,
                ],
            ],
            'milex.install.configurator.step.user' => [
                'class'        => \Milex\InstallBundle\Configurator\Step\UserStep::class,
                'tag'          => 'milex.configurator.step',
                'tagArguments' => [
                    'priority' => 2,
                ],
            ],
            'milex.install.service' => [
                'class'     => 'Milex\InstallBundle\Install\InstallService',
                'arguments' => [
                    'milex.configurator',
                    'milex.helper.cache',
                    'milex.helper.paths',
                    'doctrine.orm.entity_manager',
                    'translator',
                    'kernel',
                    'validator',
                    'security.password_encoder',
                ],
            ],
            'milex.install.leadcolumns' => [
                'class'     => \Milex\InstallBundle\EventListener\DoctrineEventSubscriber::class,
                'tag'       => 'doctrine.event_subscriber',
                'arguments' => [],
            ],
        ],
    ],
];
