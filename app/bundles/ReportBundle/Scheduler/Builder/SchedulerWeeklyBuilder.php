<?php

namespace Milex\ReportBundle\Scheduler\Builder;

use Milex\ReportBundle\Scheduler\BuilderInterface;
use Milex\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Milex\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Milex\ReportBundle\Scheduler\SchedulerInterface;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Rule;

class SchedulerWeeklyBuilder implements BuilderInterface
{
    /**
     * @return Rule
     *
     * @throws InvalidSchedulerException
     */
    public function build(Rule $rule, SchedulerInterface $scheduler)
    {
        try {
            $rule->setFreq('WEEKLY');

            if ($scheduler->isScheduledWeekDays()) {
                $days = SchedulerEnum::getWeekDays();
            } else {
                $days = [$scheduler->getScheduleDay()];
            }

            $rule->setByDay($days);
        } catch (InvalidArgument $e) {
            throw new InvalidSchedulerException();
        } catch (InvalidRRule $e) {
            throw new InvalidSchedulerException();
        }

        return $rule;
    }
}
