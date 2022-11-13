<?php

namespace Milex\ReportBundle\Adapter;

use Milex\ReportBundle\Crate\ReportDataResult;
use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Model\ReportExportOptions;
use Milex\ReportBundle\Model\ReportModel;

class ReportDataAdapter
{
    /**
     * @var ReportModel
     */
    private $reportModel;

    public function __construct(ReportModel $reportModel)
    {
        $this->reportModel = $reportModel;
    }

    public function getReportData(Report $report, ReportExportOptions $reportExportOptions)
    {
        $options                    = [];
        $options['paginate']        = true;
        $options['limit']           = $reportExportOptions->getBatchSize();
        $options['ignoreGraphData'] = true;
        $options['page']            = $reportExportOptions->getPage();
        $options['dateTo']          = $reportExportOptions->getDateTo();
        $options['dateFrom']        = $reportExportOptions->getDateFrom();

        $data = $this->reportModel->getReportData($report, null, $options);

        return new ReportDataResult($data);
    }
}
