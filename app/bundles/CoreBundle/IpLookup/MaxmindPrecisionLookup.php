<?php

namespace Milex\CoreBundle\IpLookup;

class MaxmindPrecisionLookup extends AbstractMaxmindLookup
{
    protected function getName(): string
    {
        return 'maxmind_precision';
    }
}
