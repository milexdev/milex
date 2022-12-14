<?php

namespace MilexPlugin\MilexSocialBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use MilexPlugin\MilexSocialBundle\Entity\Monitoring;

class SocialMonitorEvent extends CommonEvent
{
    /**
     * @var string
     */
    protected $integrationName;

    /**
     * @var int
     */
    protected $newLeadCount = 0;

    /**
     * @var int
     */
    protected $updatedLeadCount = 0;

    /**
     * @var array
     */
    protected $leadIds = [];

    /**
     * @param string $integrationName
     * @param int    $newLeadCount
     * @param int    $updatedLeadCount
     */
    public function __construct($integrationName, Monitoring $monitoring, array $leadIds, $newLeadCount, $updatedLeadCount)
    {
        $this->integrationName  = $integrationName;
        $this->entity           = $monitoring;
        $this->leadIds          = $leadIds;
        $this->newLeadCount     = (int) $newLeadCount;
        $this->updatedLeadCount = (int) $updatedLeadCount;
    }

    /**
     * Returns the Monitoring entity.
     *
     * @return Monitoring
     */
    public function getMonitoring()
    {
        return $this->entity;
    }

    /**
     * Get count of new leads.
     *
     * @return int
     */
    public function getNewLeadCount()
    {
        return $this->newLeadCount;
    }

    /**
     * Get count of updated leads.
     *
     * @return int
     */
    public function getUpdatedLeadCount()
    {
        return $this->updatedLeadCount;
    }

    /**
     * @return array|int
     */
    public function getTotalLeadCount()
    {
        return $this->updatedLeadCount + $this->newLeadCount;
    }

    /**
     * @return array
     */
    public function getLeadIds()
    {
        return $this->leadIds;
    }

    /**
     * @return mixed
     */
    public function getIntegrationName()
    {
        return $this->integrationName;
    }
}
