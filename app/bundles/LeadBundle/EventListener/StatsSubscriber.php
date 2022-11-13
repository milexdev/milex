<?php

namespace Milex\LeadBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\LeadBundle\Entity\CompanyChangeLog;
use Milex\LeadBundle\Entity\CompanyLead;
use Milex\LeadBundle\Entity\DoNotContact;
use Milex\LeadBundle\Entity\FrequencyRule;
use Milex\LeadBundle\Entity\LeadCategory;
use Milex\LeadBundle\Entity\LeadDevice;
use Milex\LeadBundle\Entity\LeadEventLog;
use Milex\LeadBundle\Entity\ListLead;
use Milex\LeadBundle\Entity\PointsChangeLog;
use Milex\LeadBundle\Entity\StagesChangeLog;
use Milex\LeadBundle\Entity\UtmTag;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                CompanyChangeLog::class,
                PointsChangeLog::class,
                StagesChangeLog::class,
                CompanyLead::class,
                LeadCategory::class,
                LeadDevice::class,
                LeadEventLog::class,
                ListLead::class,
                DoNotContact::class,
                FrequencyRule::class,
                UtmTag::class,
            ]
        );
    }
}
