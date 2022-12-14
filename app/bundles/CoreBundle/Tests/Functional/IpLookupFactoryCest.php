<?php

namespace Milex\CoreBundle\Tests\Functional;

use Milex\CoreBundle\Factory\IpLookupFactory;

class IpLookupFactoryCest
{
    public function ensureGettingServiceFromFactoryProvidesInstance(FunctionalTester $I)
    {
        $ipServices = $I->getParameterFromContainer('milex.ip_lookup_services');
        $ipFactory  = new IpLookupFactory($ipServices);

        foreach ($ipServices as $service => $details) {
            $instance = $ipFactory->getService($service);

            $I->assertInstanceOf(
                $details['class'],
                $instance,
                sprintf('Expected %s for service %s but received %s instead', $details['class'], $service, get_class($instance))
            );
        }
    }
}
