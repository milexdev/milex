<?php

namespace Milex\LeadBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\LeadBundle\Entity\Lead;

/**
 * Class LeadEvent.
 */
class LeadEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Lead &$lead, $isNew = false)
    {
        $this->entity = &$lead;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Lead entity.
     *
     * @return Lead
     */
    public function getLead()
    {
        return $this->entity;
    }

    /**
     * Sets the Lead entity.
     */
    public function setLead(Lead $lead)
    {
        $this->entity = $lead;
    }
}
