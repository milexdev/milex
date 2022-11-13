<?php

namespace Milex\LeadBundle\DataFixtures\ORM;

use Milex\InstallBundle\InstallFixtures\ORM\LeadFieldData;

/**
 * Class LoadLeadFieldData.
 */
class LoadLeadFieldData extends LeadFieldData
{
    /**
     * {@inheritdoc}
     */
    public static function getGroups(): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 4;
    }
}
