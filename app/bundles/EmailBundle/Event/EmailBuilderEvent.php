<?php

namespace Milex\EmailBundle\Event;

use Milex\CoreBundle\Event\BuilderEvent;
use Milex\EmailBundle\Entity\Email;

/**
 * Class EmailBuilderEvent.
 */
class EmailBuilderEvent extends BuilderEvent
{
    /**
     * @return Email|null
     */
    public function getEmail()
    {
        return $this->entity;
    }
}
