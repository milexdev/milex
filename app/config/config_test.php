<?php

use Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass;
use Milex\CoreBundle\Test\EnvLoader;
use MilexPlugin\MilexCrmBundle\Tests\Pipedrive\Mock\Client;
use Symfony\Component\DependencyInjection\Reference;

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$loader->import('config.php');

EnvLoader::load();

// Define some constants from .env
defined('MILEX_TABLE_PREFIX') || define('MILEX_TABLE_PREFIX', getenv('MILEX_DB_PREFIX') ?: '');
defined('MILEX_ENV') || define('MILEX_ENV', getenv('MILEX_ENV') ?: 'test');

$container->loadFromExtension('framework', [
    'test'    => true,
    'session' => [
        'storage_id' => 'session.storage.filesystem',
    ],
    'profiler' => [
        'collect' => false,
    ],
    'translator' => [
        'enabled' => true,
    ],
    'csrf_protection' => [
        'enabled' => true,
    ],
]);

$container->setParameter('milex.famework.csrf_protection', true);

$container
    ->register('milex_integration.pipedrive.guzzle.client', Client::class)
    ->setPublic(true);

$container->loadFromExtension('web_profiler', [
    'toolbar'             => false,
    'intercept_redirects' => false,
]);

$container->loadFromExtension('swiftmailer', [
    'disable_delivery' => true,
]);

$container->loadFromExtension('doctrine', [
    'dbal' => [
        'default_connection' => 'default',
        'connections'        => [
            'default' => [
                'driver'   => 'pdo_mysql',
                'host'     => getenv('DB_HOST') ?: '%milex.db_host%',
                'port'     => getenv('DB_PORT') ?: '%milex.db_port%',
                'dbname'   => getenv('DB_NAME') ?: '%milex.db_name%',
                'user'     => getenv('DB_USER') ?: '%milex.db_user%',
                'password' => getenv('DB_PASSWD') ?: '%milex.db_password%',
                'charset'  => 'utf8mb4',
                // Prevent Doctrine from crapping out with "unsupported type" errors due to it examining all tables in the database and not just Milex's
                'mapping_types' => [
                    'enum'  => 'string',
                    'point' => 'string',
                    'bit'   => 'string',
                ],
            ],
        ],
    ],
]);

$container->setParameter('milex.db_table_prefix', MILEX_TABLE_PREFIX);

$container->loadFromExtension('monolog', [
    'channels' => [
        'milex',
    ],
    'handlers' => [
        'main' => [
            'formatter' => 'milex.monolog.fulltrace.formatter',
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/%kernel.environment%.php',
            'level'     => getenv('MILEX_DEBUG_LEVEL') ?: 'error',
            'channels'  => [
                '!milex',
            ],
            'max_files' => 7,
        ],
        'console' => [
            'type'   => 'console',
            'bubble' => false,
        ],
        'milex' => [
            'formatter' => 'milex.monolog.fulltrace.formatter',
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/milex_%kernel.environment%.php',
            'level'     => getenv('MILEX_DEBUG_LEVEL') ?: 'error',
            'channels'  => [
                'milex',
            ],
            'max_files' => 7,
        ],
    ],
]);

$container->loadFromExtension('liip_test_fixtures', [
    'cache_db' => [
        'sqlite' => 'liip_functional_test.services_database_backup.sqlite',
    ],
    'keep_database_and_schema' => true,
]);

$loader->import('security_test.php');

// Allow overriding config without a requiring a full bundle or hacks
if (file_exists(__DIR__.'/config_override.php')) {
    $loader->import('config_override.php');
}

// Add required parameters
$container->setParameter('milex.secret_key', '68c7e75470c02cba06dd543431411e0de94e04fdf2b3a2eac05957060edb66d0');
$container->setParameter('milex.security.disableUpdates', true);
$container->setParameter('milex.rss_notification_url', null);
$container->setParameter('milex.batch_sleep_time', 0);

// Turn off creating of indexes in lead field fixtures
$container->register('milex.install.fixture.lead_field', \Milex\InstallBundle\InstallFixtures\ORM\LeadFieldData::class)
    ->addArgument(false)
    ->addTag(FixturesCompilerPass::FIXTURE_TAG)
    ->setPublic(true);
$container->register('milex.lead.fixture.contact_field', \Milex\LeadBundle\DataFixtures\ORM\LoadLeadFieldData::class)
    ->addArgument(false)
    ->addTag(FixturesCompilerPass::FIXTURE_TAG)
    ->setPublic(true);

// Use static namespace for token manager
$container->register('security.csrf.token_manager', \Symfony\Component\Security\Csrf\CsrfTokenManager::class)
    ->addArgument(new Reference('security.csrf.token_generator'))
    ->addArgument(new Reference('security.csrf.token_storage'))
    ->addArgument('test')
    ->setPublic(true);

// HTTP client mock handler providing response queue
$container->register('milex.http.client.mock_handler', \GuzzleHttp\Handler\MockHandler::class)
    ->setClass('\GuzzleHttp\Handler\MockHandler');

// Stub Guzzle HTTP client to prevent accidental request to third parties
$container->register('milex.http.client', \GuzzleHttp\Client::class)
    ->setPublic(true)
    ->setFactory('\Milex\CoreBundle\Test\Guzzle\ClientFactory::stub')
    ->addArgument(new Reference('milex.http.client.mock_handler'));
