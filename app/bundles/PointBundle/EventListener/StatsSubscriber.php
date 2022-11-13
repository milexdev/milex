<?php

namespace Milex\PointBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\PointBundle\Entity\LeadPointLog;
use Milex\PointBundle\Entity\LeadTriggerLog;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                LeadPointLog::class,
                LeadTriggerLog::class,
            ]
        );
    }
}
