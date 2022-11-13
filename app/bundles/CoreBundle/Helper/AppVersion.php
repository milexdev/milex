<?php

namespace Milex\CoreBundle\Helper;

class AppVersion
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return MILEX_VERSION;
    }
}
