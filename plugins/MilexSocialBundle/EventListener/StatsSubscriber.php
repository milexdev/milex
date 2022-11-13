<?php

namespace MilexPlugin\MilexSocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use MilexPlugin\MilexSocialBundle\Entity\TweetStat;
use MilexPlugin\MilexSocialBundle\Entity\TweetStatRepository;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);

        /** @var TweetStatRepository $repo */
        $repo                      = $entityManager->getRepository(TweetStat::class);
        $table                     = $repo->getTableName();
        $this->repositories[]      = $repo;
        $this->permissions[$table] = ['tweet' => 'milexSocial:tweets'];
    }
}
