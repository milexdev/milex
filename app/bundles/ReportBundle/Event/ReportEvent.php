<?php

namespace Milex\ReportBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\ReportBundle\Entity\Report;

/**
 * Class ReportEvent.
 */
class ReportEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Report $report, $isNew = false)
    {
        $this->entity = $report;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Report entity.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->entity;
    }

    /**
     * Sets the Report entity.
     */
    public function setReport(Report $report)
    {
        $this->entity = $report;
    }
}
