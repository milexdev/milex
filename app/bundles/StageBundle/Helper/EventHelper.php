<?php

namespace Milex\StageBundle\Helper;

use Milex\LeadBundle\Entity\Lead;

/**
 * Class EventHelper.
 */
class EventHelper
{
    /**
     * @param Lead  $lead
     * @param array $action
     *
     * @return int
     */
    public static function engageStageAction($lead, $action)
    {
        static $initiated = [];

        return 0;
    }
}
