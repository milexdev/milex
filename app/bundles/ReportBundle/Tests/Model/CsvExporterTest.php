<?php

namespace Milex\ReportBundle\Tests\Model;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Templating\Helper\DateHelper;
use Milex\CoreBundle\Templating\Helper\FormatterHelper;
use Milex\ReportBundle\Crate\ReportDataResult;
use Milex\ReportBundle\Model\CsvExporter;
use Milex\ReportBundle\Tests\Fixtures;
use Symfony\Component\Translation\TranslatorInterface;

class CsvExporterTest extends \PHPUnit\Framework\TestCase
{
    public function testExport()
    {
        $dateHelperMock = $this->createMock(DateHelper::class);

        $dateHelperMock->expects($this->any())
            ->method('toFullConcat')
            ->willReturn('2017-10-01');

        $translator = $this->createMock(TranslatorInterface::class);

        $coreParametersHelperMock = $this->createMock(CoreParametersHelper::class);

        $formatterHelperMock = new FormatterHelper($dateHelperMock, $translator);

        $reportDataResult = new ReportDataResult(Fixtures::getValidReportResult());

        $csvExporter = new CsvExporter($formatterHelperMock, $coreParametersHelperMock);

        $tmpFile = tempnam(sys_get_temp_dir(), 'milex_csv_export_test_');
        $file    = fopen($tmpFile, 'w');

        $csvExporter->export($reportDataResult, $file);

        fclose($file);

        $result = array_map('str_getcsv', file($tmpFile));

        $expected = [
            [
                'City',
                'Company',
                'Country',
                'Date identified',
                'Email',
            ],
            [
                'City',
                '',
                '',
                '',
                '',
            ],
            [
                '',
                'Company',
                '',
                '',
                '',
            ],
            [
                '',
                '',
                'Country',
                '',
                '',
            ],
            [
                '',
                'ConnectWise',
                '',
                '2017-10-01',
                'connectwise@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'mytest@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'john@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'bogus@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'date-test@example.com',
            ],
            [
                '',
                'Bodega Club',
                '',
                '2017-10-01',
                'club@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'test@example.com',
            ],
            [
                '',
                '',
                '',
                '2017-10-01',
                'test@example.com',
            ],
        ];

        $this->assertSame($expected, $result);

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }
}
