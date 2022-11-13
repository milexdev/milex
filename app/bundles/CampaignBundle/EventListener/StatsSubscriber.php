<?php

namespace Milex\CampaignBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CampaignBundle\Entity\Lead;
use Milex\CampaignBundle\Entity\LeadEventLog;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                Lead::class,
                LeadEventLog::class,
            ]
        );
    }
}
