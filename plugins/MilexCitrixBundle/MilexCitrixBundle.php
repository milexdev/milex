<?php

namespace MilexPlugin\MilexCitrixBundle;

use Milex\PluginBundle\Bundle\PluginBundleBase;
use MilexPlugin\MilexCitrixBundle\Helper\CitrixHelper;

/**
 * Class MilexCitrixBundle.
 */
class MilexCitrixBundle extends PluginBundleBase
{
    public function boot()
    {
        parent::boot();

        CitrixHelper::init(
            $this->container->get('milex.helper.integration'),
            $this->container->get('monolog.logger.milex'),
            $this->container->get('router')
        );
    }
}
