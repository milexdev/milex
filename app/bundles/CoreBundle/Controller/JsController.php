<?php

namespace Milex\CoreBundle\Controller;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\BuildJsEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsController.
 */
class JsController extends CommonController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        // Don't store a visitor with this request
        defined('MILEX_NON_TRACKABLE_REQUEST') || define('MILEX_NON_TRACKABLE_REQUEST', 1);

        $dispatcher = $this->dispatcher;
        $debug      = $this->factory->getKernel()->isDebug();
        $event      = new BuildJsEvent($this->getJsHeader(), $debug);

        if ($dispatcher->hasListeners(CoreEvents::BUILD_MILEX_JS)) {
            $dispatcher->dispatch(CoreEvents::BUILD_MILEX_JS, $event);
        }

        return new Response($event->getJs(), 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Build a JS header for the Milex embedded JS.
     *
     * @return string
     */
    protected function getJsHeader()
    {
        $year = date('Y');

        return <<<JS
/**
 * @package     MilexJS
 * @copyright   {$year} Milex Contributors. All rights reserved.
 * @author      Milex
 * @link        http://milex.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
JS;
    }
}
