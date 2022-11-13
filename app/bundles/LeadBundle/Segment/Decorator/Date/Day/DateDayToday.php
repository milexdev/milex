<?php

namespace Milex\LeadBundle\Segment\Decorator\Date\Day;

use Milex\CoreBundle\Helper\DateTimeHelper;

class DateDayToday extends DateDayAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper)
    {
    }
}
