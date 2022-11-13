<?php

namespace Milex\StageBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\StageBundle\Entity\Stage;

/**
 * Class StageEvent.
 */
class StageEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Stage &$stage, $isNew = false)
    {
        $this->entity = &$stage;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Stage entity.
     *
     * @return Stage
     */
    public function getStage()
    {
        return $this->entity;
    }

    /**
     * Sets the Stage entity.
     */
    public function setStage(Stage $stage)
    {
        $this->entity = $stage;
    }
}
