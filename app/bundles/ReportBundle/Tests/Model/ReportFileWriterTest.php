<?php

namespace Milex\ReportBundle\Tests\Model;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\ReportBundle\Crate\ReportDataResult;
use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Entity\Scheduler;
use Milex\ReportBundle\Model\CsvExporter;
use Milex\ReportBundle\Model\ExportHandler;
use Milex\ReportBundle\Model\ReportExportOptions;
use Milex\ReportBundle\Model\ReportFileWriter;
use Milex\ReportBundle\Tests\Fixtures;

class ReportFileWriterTest extends \PHPUnit\Framework\TestCase
{
    public function testWriteReportData()
    {
        $csvExporter = $this->getMockBuilder(CsvExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = 'Handler';

        $report    = new Report();
        $scheduler = new Scheduler($report, new \DateTime());

        $reportDataResult = new ReportDataResult(Fixtures::getValidReportResult());

        $coreParametersHelper = $this->getMockBuilder(CoreParametersHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $coreParametersHelper->expects($this->once())
            ->method('get')
            ->with('report_export_batch_size')
            ->willReturn(3);

        $reportExportOptions = new ReportExportOptions($coreParametersHelper);

        $exportHandler->expects($this->once())
            ->method('getHandler')
            ->willReturn($handler);

        $csvExporter->expects($this->once())
            ->method('export')
            ->with($reportDataResult, $handler, 1)
            ->willReturn($handler);

        $exportHandler->expects($this->once())
            ->method('closeHandler')
            ->willReturn($handler);

        $reportFileWriter = new ReportFileWriter($csvExporter, $exportHandler);

        $reportFileWriter->writeReportData($scheduler, $reportDataResult, $reportExportOptions);
    }

    public function testClear()
    {
        $csvExporter = $this->getMockBuilder(CsvExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $report    = new Report();
        $scheduler = new Scheduler($report, new \DateTime());

        $exportHandler->expects($this->once())
            ->method('removeFile');

        $reportFileWriter = new ReportFileWriter($csvExporter, $exportHandler);

        $reportFileWriter->clear($scheduler);
    }

    public function testGetFilePath()
    {
        $csvExporter = $this->getMockBuilder(CsvExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $report    = new Report();
        $scheduler = new Scheduler($report, new \DateTime());

        $exportHandler->expects($this->once())
            ->method('getPath');

        $reportFileWriter = new ReportFileWriter($csvExporter, $exportHandler);

        $reportFileWriter->getFilePath($scheduler);
    }
}
