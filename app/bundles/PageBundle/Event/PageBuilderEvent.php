<?php

namespace Milex\PageBundle\Event;

use Milex\CoreBundle\Event\BuilderEvent;
use Milex\PageBundle\Entity\Page;

/**
 * Class PageBuilderEvent.
 */
class PageBuilderEvent extends BuilderEvent
{
    /**
     * @return Page|null
     */
    public function getPage()
    {
        return $this->entity;
    }
}
