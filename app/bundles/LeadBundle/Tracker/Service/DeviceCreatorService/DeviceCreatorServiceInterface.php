<?php

namespace Milex\LeadBundle\Tracker\Service\DeviceCreatorService;

use DeviceDetector\DeviceDetector;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadDevice;

/**
 * Interface DeviceCreatorServiceInterface.
 */
interface DeviceCreatorServiceInterface
{
    /**
     * @return LeadDevice|null Null is returned if device can't be detected
     */
    public function getCurrentFromDetector(DeviceDetector $deviceDetector, Lead $assignedLead);
}
