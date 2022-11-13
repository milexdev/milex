<?php

namespace Milex\CampaignBundle\Tests\EventCollector\Accessor\Event;

use Milex\CampaignBundle\EventCollector\Accessor\Event\ConditionAccessor;

class ConditionAccessorTest extends \PHPUnit\Framework\TestCase
{
    public function testEventNameIsReturned()
    {
        $accessor = new ConditionAccessor(['eventName' => 'test']);

        $this->assertEquals('test', $accessor->getEventName());
    }

    public function testExtraParamIsReturned()
    {
        $accessor = new ConditionAccessor(['eventName' => 'test', 'foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $accessor->getExtraProperties());
    }
}
