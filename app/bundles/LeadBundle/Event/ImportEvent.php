<?php

namespace Milex\LeadBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\LeadBundle\Entity\Import;

/**
 * Class ImportEvent.
 */
class ImportEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Import $entity, $isNew)
    {
        $this->entity = $entity;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Import entity.
     *
     * @return Import
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
