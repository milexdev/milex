<?php

namespace Milex\NotificationBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\NotificationBundle\Entity\Notification;

/**
 * Class NotificationEvent.
 */
class NotificationEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Notification $notification, $isNew = false)
    {
        $this->entity = $notification;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Notification entity.
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->entity;
    }

    /**
     * Sets the Notification entity.
     */
    public function setNotification(Notification $notification)
    {
        $this->entity = $notification;
    }
}
