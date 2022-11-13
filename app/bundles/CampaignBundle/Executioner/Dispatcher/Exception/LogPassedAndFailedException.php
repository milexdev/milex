<?php

namespace Milex\CampaignBundle\Executioner\Dispatcher\Exception;

use Milex\CampaignBundle\Entity\LeadEventLog;

class LogPassedAndFailedException extends \Exception
{
    /**
     * LogNotProcessedException constructor.
     */
    public function __construct(LeadEventLog $log)
    {
        parent::__construct("LeadEventLog ID # {$log->getId()} was passed to both pass() or fail(). Pass or fail the log, not both.", 0, null);
    }
}
