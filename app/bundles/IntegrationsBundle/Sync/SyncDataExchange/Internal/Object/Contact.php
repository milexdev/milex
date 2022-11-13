<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object;

use Milex\LeadBundle\Entity\Lead;

final class Contact implements ObjectInterface
{
    const NAME   = 'lead'; // kept as lead for BC
    const ENTITY = Lead::class;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName(): string
    {
        return self::ENTITY;
    }
}
