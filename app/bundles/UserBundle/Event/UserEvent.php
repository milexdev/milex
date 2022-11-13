<?php

namespace Milex\UserBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\UserBundle\Entity\User;

/**
 * Class UserEvent.
 */
class UserEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(User &$user, $isNew = false)
    {
        $this->entity = &$user;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the User entity.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->entity;
    }

    /**
     * Sets the User entity.
     */
    public function setUser(User $user)
    {
        $this->entity = $user;
    }
}
