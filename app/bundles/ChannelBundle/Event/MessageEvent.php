<?php

namespace Milex\ChannelBundle\Event;

use Milex\ChannelBundle\Entity\Message;
use Milex\CoreBundle\Event\CommonEvent;

class MessageEvent extends CommonEvent
{
    /**
     * MessageEvent constructor.
     *
     * @param bool $isNew
     */
    public function __construct(Message $message, $isNew = false)
    {
        $this->entity = $message;
        $this->isNew  = $isNew;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->entity;
    }
}
