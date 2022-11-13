<?php

namespace MilexPlugin\MilexCrmBundle;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Factory\MilexFactory;
use Milex\PluginBundle\Bundle\PluginBundleBase;
use Milex\PluginBundle\Entity\Plugin;

/**
 * Class MilexCrmBundle.
 */
class MilexCrmBundle extends PluginBundleBase
{
    public static function onPluginInstall(Plugin $plugin, MilexFactory $factory, $metadata = null, $installedSchema = null)
    {
        if (null === $metadata) {
            $metadata = self::getMetadata($factory->getEntityManager());
        }

        if (null !== $metadata) {
            parent::onPluginInstall($plugin, $factory, $metadata, $installedSchema);
        }
    }

    /**
     * Fix: plugin installer doesn't find metadata entities for the plugin
     * PluginBundle/Controller/PluginController:410.
     *
     * @return array|null
     */
    private static function getMetadata(EntityManager $em)
    {
        $allMetadata   = $em->getMetadataFactory()->getAllMetadata();
        $currentSchema = $em->getConnection()->getSchemaManager()->createSchema();

        $classes = [];

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $meta */
        foreach ($allMetadata as $meta) {
            if (false === strpos($meta->namespace, 'MilexPlugin\\MilexCrmBundle')) {
                continue;
            }

            $table = $meta->getTableName();

            if ($currentSchema->hasTable($table)) {
                continue;
            }

            $classes[] = $meta;
        }

        return $classes ?: null;
    }
}
