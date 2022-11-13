<?php

namespace Milex\UserBundle\Tests\Event;

use Milex\UserBundle\Entity\User;
use Milex\UserBundle\Event\LoginEvent;

class LoginEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetUser()
    {
        $user  = $this->createMock(User::class);
        $event = new LoginEvent($user);

        $this->assertEquals($user, $event->getUser());
    }
}
