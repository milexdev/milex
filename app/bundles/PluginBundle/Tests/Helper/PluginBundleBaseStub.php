<?php

namespace Milex\PluginBundle\Tests\Helper;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Factory\MilexFactory;
use Milex\PluginBundle\Entity\Plugin;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * A stub Base Bundle class which implements stub methods for testing purposes.
 */
abstract class PluginBundleBaseStub extends Bundle
{
    /**
     * @param null $metadata
     * @param null $installedSchema
     *
     * @throws \Exception
     */
    public static function onPluginInstall(Plugin $plugin, MilexFactory $factory, $metadata = null, $installedSchema = null)
    {
    }

    /**
     * Called by PluginController::reloadAction when the addon version does not match what's installed.
     *
     * @param null   $metadata
     * @param Schema $installedSchema
     *
     * @throws \Exception
     */
    public static function onPluginUpdate(Plugin $plugin, MilexFactory $factory, $metadata = null, Schema $installedSchema = null)
    {
    }
}
