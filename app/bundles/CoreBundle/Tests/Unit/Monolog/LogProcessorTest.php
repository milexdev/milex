<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Tests\Unit\Monolog;

use DateTime;
use Milex\CoreBundle\Monolog\LogProcessor;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class LogProcessorTest extends TestCase
{
    public function testLogProcessor(): void
    {
        $logProcessor = new LogProcessor();
        $record       = [
            'message'    => 'This is debug message',
            'context'    => [],
            'level'      => 100,
            'level_name' => 'DEBUG',
            'channel'    => 'milex',
            'datetime'   => new DateTime(),
            'extra'      => [],
        ];
        $outputRecord = $logProcessor($record);

        $record['extra']['hostname'] = gethostname();
        $record['extra']['pid']      = getmypid();

        Assert::assertSame($record, $outputRecord);
    }
}
