<?php

namespace Milex\EmailBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\EmailBundle\Entity\EmailReply;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\Entity\StatDevice;
use Milex\EmailBundle\Entity\StatDeviceRepository;
use MilexPlugin\MilexFocusBundle\Entity\StatRepository;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);

        /** @var StatDeviceRepository $repo */
        $repo                                     = $entityManager->getRepository(StatDevice::class);
        $this->repositories[]                     = $repo;
        $this->permissions[$repo->getTableName()] = ['stat.lead' => 'lead:leads'];

        $this->addContactRestrictedRepositories([EmailReply::class]);

        /** @var StatRepository $repo */
        $repo                           = $entityManager->getRepository(Stat::class);
        $this->repositories[]           = $repo;
        $statsTable                     = $repo->getTableName();
        $this->permissions[$statsTable] = ['lead' => 'lead:leads'];
        $this->selects[$statsTable]     = [
            'id',
            'email_id',
            'lead_id',
            'list_id',
            'ip_id',
            'email_address',
            'date_sent',
            'is_read',
            'is_failed',
            'viewed_in_browser',
            'date_read',
            'tracking_hash',
            'retry_count',
            'source',
            'source_id',
            'open_count',
            'last_opened',
            'open_details',
        ];
    }
}
