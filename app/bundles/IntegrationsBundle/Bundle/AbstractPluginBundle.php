<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Bundle;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Milex\CoreBundle\Factory\MilexFactory;
use Milex\IntegrationsBundle\Migration\Engine;
use Milex\PluginBundle\Bundle\PluginBundleBase;
use Milex\PluginBundle\Entity\Plugin;

/**
 * Base Bundle class which should be extended by addon bundles.
 */
abstract class AbstractPluginBundle extends PluginBundleBase
{
    /**
     * @param array|null $metadata
     *
     * @throws Exception
     */
    public static function onPluginUpdate(Plugin $plugin, MilexFactory $factory, $metadata = null, ?Schema $installedSchema = null): void
    {
        $entityManager = $factory->getEntityManager();
        $tablePrefix   = (string) $factory->getParameter('milex.db_table_prefix');

        $migrationEngine = new Engine(
            $entityManager,
            $tablePrefix,
            __DIR__.'/../../../../plugins/'.$plugin->getBundle(),
            $plugin->getBundle()
        );

        if (method_exists(__CLASS__, 'installAllTablesIfMissing')) {
            static::installAllTablesIfMissing(
                $entityManager->getConnection()->getSchemaManager()->createSchema(),
                $tablePrefix,
                $factory,
                $metadata
            );
        }

        $migrationEngine->up();
    }
}
