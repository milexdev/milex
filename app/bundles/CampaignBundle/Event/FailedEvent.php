<?php

namespace Milex\CampaignBundle\Event;

use Milex\CampaignBundle\Entity\LeadEventLog;
use Milex\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;

class FailedEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var AbstractEventAccessor
     */
    private $config;

    /**
     * @var LeadEventLog
     */
    private $log;

    /**
     * FailedEvent constructor.
     */
    public function __construct(AbstractEventAccessor $config, LeadEventLog $log)
    {
        $this->config = $config;
        $this->log    = $log;
    }

    /**
     * @return AbstractEventAccessor
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return LeadEventLog
     */
    public function getLog()
    {
        return $this->log;
    }
}
