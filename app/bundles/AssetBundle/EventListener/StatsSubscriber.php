<?php

namespace Milex\AssetBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\AssetBundle\Entity\Download;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories([Download::class]);
    }
}
