<?php

namespace Milex\DynamicContentBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Milex\CoreBundle\Controller\AjaxLookupControllerTrait;

class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;
}
