<?php

namespace Milex\CoreBundle\Tests\Unit\Event;

use Milex\CoreBundle\Event\CustomTemplateEvent;

class CustomTemplateEventTest extends \PHPUnit\Framework\TestCase
{
    public function testNullRequestDoesNotThrowException()
    {
        $event = new CustomTemplateEvent(null, 'test');
        $this->assertSame('test', $event->getTemplate());
    }

    public function testEmptyTemplateThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CustomTemplateEvent();
    }
}
