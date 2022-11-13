<?php

namespace Milex\DynamicContentBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\DynamicContentBundle\Entity\DynamicContentLeadData;
use Milex\DynamicContentBundle\Entity\Stat;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                Stat::class,
                DynamicContentLeadData::class,
            ]
        );
    }
}
