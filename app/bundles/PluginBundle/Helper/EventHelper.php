<?php

namespace Milex\PluginBundle\Helper;

use Milex\CoreBundle\Factory\MilexFactory;
use Milex\PluginBundle\EventListener\PushToIntegrationTrait;

/**
 * Class EventHelper.
 */
class EventHelper
{
    use PushToIntegrationTrait;

    /**
     * @param $lead
     */
    public static function pushLead($config, $lead, MilexFactory $factory)
    {
        $contact = $factory->getEntityManager()->getRepository('MilexLeadBundle:Lead')->getEntityWithPrimaryCompany($lead);

        /** @var \Milex\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $factory->getHelper('integration');

        static::setStaticIntegrationHelper($integrationHelper);
        $errors  = [];

        return static::pushIt($config, $contact, $errors);
    }
}
