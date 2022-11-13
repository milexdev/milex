<?php

namespace Milex\CampaignBundle\Tests\EventCollector\Accessor\Event;

use Milex\CampaignBundle\EventCollector\Accessor\Event\DecisionAccessor;

class DecisionAccessorTest extends \PHPUnit\Framework\TestCase
{
    public function testEventNameIsReturned()
    {
        $accessor = new DecisionAccessor(['eventName' => 'test']);

        $this->assertEquals('test', $accessor->getEventName());
    }

    public function testExtraParamIsReturned()
    {
        $accessor = new DecisionAccessor(['eventName' => 'test', 'foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $accessor->getExtraProperties());
    }
}
