<?php

namespace Milex\LeadBundle\Segment\Decorator;

use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;

class DateDecorator extends CustomMappedDecorator
{
    /**
     * @throws \Exception
     */
    public function getParameterValue(ContactSegmentFilterCrate $contactSegmentFilterCrate)
    {
        throw new \Exception('Instance of Date option needs to implement this function');
    }
}
