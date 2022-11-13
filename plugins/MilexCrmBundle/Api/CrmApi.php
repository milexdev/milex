<?php

namespace MilexPlugin\MilexCrmBundle\Api;

use MilexPlugin\MilexCrmBundle\Integration\CrmAbstractIntegration;

/**
 * Class CrmApi.
 *
 * @method createLead
 */
class CrmApi
{
    protected $integration;

    public function __construct(CrmAbstractIntegration $integration)
    {
        $this->integration = $integration;
    }
}
