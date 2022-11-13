<?php

namespace MilexPlugin\MilexCrmBundle\Tests\Stubs;

use MilexPlugin\MilexCrmBundle\Integration\CrmAbstractIntegration;

class StubIntegration extends CrmAbstractIntegration
{
    public function getName()
    {
        return 'Stub';
    }
}
