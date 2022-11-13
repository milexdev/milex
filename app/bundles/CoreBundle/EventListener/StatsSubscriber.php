<?php

namespace Milex\CoreBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Entity\AuditLog;
use Milex\CoreBundle\Entity\IpAddress;
use Milex\CoreBundle\Security\Permissions\CorePermissions;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->repositories['MilexCoreBundle:AuditLog'] = $entityManager->getRepository(AuditLog::class);
        $this->permissions['MilexCoreBundle:AuditLog']  = ['admin'];

        $this->repositories['MilexCoreBundle:IpAddress'] = $entityManager->getRepository(IpAddress::class);
    }
}
