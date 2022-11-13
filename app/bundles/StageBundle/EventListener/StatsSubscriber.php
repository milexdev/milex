<?php

namespace Milex\StageBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\StageBundle\Entity\LeadStageLog;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories([LeadStageLog::class]);
    }
}
