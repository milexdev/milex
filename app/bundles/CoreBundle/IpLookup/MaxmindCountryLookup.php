<?php

namespace Milex\CoreBundle\IpLookup;

class MaxmindCountryLookup extends AbstractMaxmindLookup
{
    protected function getName(): string
    {
        return 'maxmind_country';
    }
}
