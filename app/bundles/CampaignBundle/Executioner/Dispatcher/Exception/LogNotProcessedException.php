<?php

namespace Milex\CampaignBundle\Executioner\Dispatcher\Exception;

use Milex\CampaignBundle\Entity\LeadEventLog;

class LogNotProcessedException extends \Exception
{
    /**
     * LogNotProcessedException constructor.
     */
    public function __construct(LeadEventLog $log)
    {
        parent::__construct("LeadEventLog ID # {$log->getId()} must be passed to either pass() or fail()", 0, null);
    }
}
