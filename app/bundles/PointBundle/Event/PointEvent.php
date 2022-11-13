<?php

namespace Milex\PointBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\PointBundle\Entity\Point;

class PointEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Point &$point, $isNew = false)
    {
        $this->entity = &$point;
        $this->isNew  = $isNew;
    }

    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->entity;
    }

    public function setPoint(Point $point)
    {
        $this->entity = $point;
    }
}
