<?php

namespace Milex\CoreBundle\Tests\Unit\Helper;

use Milex\CoreBundle\Helper\ClickthroughHelper;
use Milex\CoreBundle\Tests\Unit\Helper\TestResources\WakeupCall;

class ClickthroughHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testEncodingCanBeDecoded()
    {
        $array = ['foo' => 'bar'];

        $this->assertEquals($array, ClickthroughHelper::decodeArrayFromUrl(ClickthroughHelper::encodeArrayForUrl($array)));
    }

    /**
     * @covers \Milex\CoreBundle\Helper\Serializer::decode
     */
    public function testObjectInArrayIsDetectedOrIgnored()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = ['foo' => new WakeupCall()];

        ClickthroughHelper::decodeArrayFromUrl(ClickthroughHelper::encodeArrayForUrl($array));
    }

    public function testOnlyArraysCanBeDecodedToPreventObjectWakeupVulnerability()
    {
        $this->expectException(\InvalidArgumentException::class);

        ClickthroughHelper::decodeArrayFromUrl(urlencode(base64_encode(serialize(new \stdClass()))));
    }

    public function testEmptyStringDoesNotThrowException()
    {
        $array = [];

        $this->assertEquals($array, ClickthroughHelper::decodeArrayFromUrl(''));
    }
}
