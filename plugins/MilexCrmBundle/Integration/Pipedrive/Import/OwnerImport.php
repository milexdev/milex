<?php

namespace MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Import;

use MilexPlugin\MilexCrmBundle\Entity\PipedriveOwner;

class OwnerImport extends AbstractImport
{
    /**
     * @return bool
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $data = [])
    {
        $pipedriveOwner = $this->em->getRepository(PipedriveOwner::class)->findOneByOwnerId($data['id']);

        if (!$pipedriveOwner) {
            $pipedriveOwner = new PipedriveOwner();
        }

        $pipedriveOwner->setEmail($data['email']);
        $pipedriveOwner->setOwnerId($data['id']);

        $this->em->persist($pipedriveOwner);
        $this->em->flush();

        return true;
    }
}
