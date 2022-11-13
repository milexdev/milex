<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// This is loaded by \Milex\CoreBundle\DependencyInjection\MilexCoreExtension to auto-wire Commands
// as they were done in M3 which must be done when the bundle config.php's services are processed to prevent
// Symfony attempting to auto-wire commands manually registered by bundle

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure() // Automatically registers services as commands as was in M3
        ->public() // Set as public as was the default in M3
    ;

    // Auto-register Commands as it worked in M3
    $services->load('Milex\\', '../bundles/*/Command/*');
    $services->load('MilexPlugin\\', '../../plugins/*/Command/*');
};
