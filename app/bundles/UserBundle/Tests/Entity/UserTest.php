<?php

namespace Milex\UserBundle\Tests\Entity;

use Milex\UserBundle\Entity\User;

class UserTest extends \PHPUnit\Framework\TestCase
{
    public function testUserIsGuest()
    {
        $user = new User(true);
        $this->assertTrue($user->isGuest());
    }

    public function testUserIsNotGuest()
    {
        $user = new User();
        $this->assertFalse($user->isGuest());
    }
}
