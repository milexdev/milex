<?php

namespace Milex\CoreBundle\IpLookup;

class MaxmindOmniLookup extends AbstractMaxmindLookup
{
    protected function getName(): string
    {
        return 'maxmind_omni';
    }
}
