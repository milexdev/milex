<?php

namespace Milex\LeadBundle\Segment\Decorator\Date\Day;

use Milex\CoreBundle\Helper\DateTimeHelper;

class DateDayTomorrow extends DateDayAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper)
    {
        $dateTimeHelper->modify('+1 day');
    }
}
