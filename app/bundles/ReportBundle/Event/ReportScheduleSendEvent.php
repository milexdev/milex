<?php

namespace Milex\ReportBundle\Event;

use Milex\ReportBundle\Entity\Scheduler;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ReportScheduleSendEvent.
 */
class ReportScheduleSendEvent extends Event
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $file
     */
    public function __construct(Scheduler $scheduler, $file)
    {
        $this->scheduler = $scheduler;
        $this->file      = $file;
    }

    /**
     * @return Scheduler
     */
    public function getScheduler()
    {
        return $this->scheduler;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
