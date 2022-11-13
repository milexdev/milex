<?php

namespace MilexPlugin\MilexCitrixBundle\Entity;

use MilexPlugin\MilexCitrixBundle\Helper\BasicEnum;

abstract class CitrixEventTypes extends BasicEnum
{
    // Used for querying events
    const STARTED    = 'started';
    const REGISTERED = 'registered';
    const ATTENDED   = 'attended';
}
