<?php

namespace Milex\PointBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\PointBundle\Entity\Trigger;

class TriggerEvent extends CommonEvent
{
    /**
     * @var Trigger
     */
    protected $entity;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @param bool $isNew
     */
    public function __construct(Trigger &$trigger, $isNew = false)
    {
        $this->entity = &$trigger;
        $this->isNew  = $isNew;
    }

    /**
     * @return Trigger
     */
    public function getTrigger()
    {
        return $this->entity;
    }

    public function setTrigger(Trigger $trigger)
    {
        $this->entity = $trigger;
    }
}
