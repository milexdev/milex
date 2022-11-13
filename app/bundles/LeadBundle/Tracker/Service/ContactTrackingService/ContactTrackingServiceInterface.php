<?php

namespace Milex\LeadBundle\Tracker\Service\ContactTrackingService;

use Milex\LeadBundle\Entity\Lead;

/**
 * Interface ContactTrackingInterface.
 */
interface ContactTrackingServiceInterface
{
    /**
     * Return current tracked Lead.
     *
     * @return Lead|null
     */
    public function getTrackedLead();

    /**
     * @return string|null Unique identifier
     */
    public function getTrackedIdentifier();
}
