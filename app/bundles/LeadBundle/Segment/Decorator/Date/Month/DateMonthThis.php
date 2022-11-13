<?php

namespace Milex\LeadBundle\Segment\Decorator\Date\Month;

use Milex\CoreBundle\Helper\DateTimeHelper;

class DateMonthThis extends DateMonthAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper)
    {
        $dateTimeHelper->setDateTime('midnight first day of this month', null);
    }
}
