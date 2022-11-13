<?php

namespace Milex\UserBundle\Event;

use Milex\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class LoginEvent.
 */
class LoginEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \Milex\UserBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
