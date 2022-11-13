<?php

namespace Milex\PageBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\EventListener\CommonStatsSubscriber;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\PageBundle\Entity\Hit;
use Milex\PageBundle\Entity\Redirect;
use Milex\PageBundle\Entity\Trackable;
use Milex\PageBundle\Entity\VideoHit;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                Hit::class,
                VideoHit::class,
            ]
        );

        $this->repositories[] = $entityManager->getRepository(Redirect::class);
        $this->repositories[] = $entityManager->getRepository(Trackable::class);
    }
}
