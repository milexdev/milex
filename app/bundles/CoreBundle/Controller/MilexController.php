<?php

namespace Milex\CoreBundle\Controller;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Interface MilexController.
 *
 * A dummy interface to ensure that only Milex bundles are affected by Milex onKernelController events
 */
interface MilexController
{
    /**
     * Initialize the controller.
     *
     * @return mixed
     */
    public function initialize(FilterControllerEvent $event);
}
