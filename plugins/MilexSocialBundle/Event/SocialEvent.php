<?php

namespace MilexPlugin\MilexSocialBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use MilexPlugin\MilexSocialBundle\Entity\Monitoring;

/**
 * Class SocialEvent.
 */
class SocialEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Monitoring $monitoring, $isNew = false)
    {
        $this->entity = $monitoring;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Monitoring entity.
     *
     * @return Monitoring
     */
    public function getMonitoring()
    {
        return $this->entity;
    }

    /**
     * Sets the Monitoring entity.
     */
    public function setMonitoring(Monitoring $monitoring)
    {
        $this->entity = $monitoring;
    }
}
