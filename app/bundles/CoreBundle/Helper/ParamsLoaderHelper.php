<?php

namespace Milex\CoreBundle\Helper;

use Milex\Middleware\ConfigAwareTrait;

class ParamsLoaderHelper
{
    use ConfigAwareTrait;

    private $parameters = [];

    /**
     * Get parameters for static method.
     *
     * @return array
     */
    public function getParameters()
    {
        if (empty($this->parameters)) {
            $this->parameters = $this->getConfig();
        }

        return $this->parameters;
    }
}
