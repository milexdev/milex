<?php

namespace Milex\CoreBundle\Tests\Unit\DependencyInjection\EnvProcessor;

use Milex\CoreBundle\DependencyInjection\EnvProcessor\NullableProcessor;
use PHPUnit\Framework\TestCase;

class NullableProcessorTest extends TestCase
{
    public function testNullReturnedIfEmptyString()
    {
        $getEnv = function (string $name) {
            return '';
        };

        $processor = new NullableProcessor();

        $value = $processor->getEnv('', 'test', $getEnv);

        $this->assertNull($value);
    }

    public function testValueReturnedIfNotEmptyString()
    {
        $getEnv = function (string $name) {
            return 'foobar';
        };

        $processor = new NullableProcessor();

        $value = $processor->getEnv('', 'test', $getEnv);

        $this->assertEquals('foobar', $value);
    }
}
