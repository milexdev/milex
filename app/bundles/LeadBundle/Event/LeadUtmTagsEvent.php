<?php

namespace Milex\LeadBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\LeadBundle\Entity\Lead;

/**
 * Class PointsChangeEvent.
 */
class LeadUtmTagsEvent extends CommonEvent
{
    protected $utmtags;

    /**
     * @param bool $utmTag
     */
    public function __construct(Lead $lead, $utmTag)
    {
        $this->entity  = $lead;
        $this->utmtags = $utmTag;
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
     * Returns the new points.
     *
     * @return int
     */
    public function getUtmTags()
    {
        return $this->utmtags;
    }
}
