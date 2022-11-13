<?php

namespace Milex\ReportBundle\Tests\Scheduler\Option;

use Milex\ReportBundle\Scheduler\Option\ExportOption;

class ExportOptionTest extends \PHPUnit\Framework\TestCase
{
    public function testReportId()
    {
        $exportOption = new ExportOption(11);

        $this->assertSame(11, $exportOption->getReportId());
    }

    public function testNoReportId()
    {
        $exportOption = new ExportOption(null);

        $this->assertSame(0, $exportOption->getReportId());
    }

    public function testBadFormatOfId()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ExportOption('string');
    }
}
