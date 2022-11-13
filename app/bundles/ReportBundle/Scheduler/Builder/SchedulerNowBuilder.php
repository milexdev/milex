<?php

declare(strict_types=1);

namespace Milex\ReportBundle\Scheduler\Builder;

use Milex\ReportBundle\Scheduler\BuilderInterface;
use Milex\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Milex\ReportBundle\Scheduler\SchedulerInterface;
use Recurr\Exception\InvalidArgument;
use Recurr\Rule;

class SchedulerNowBuilder implements BuilderInterface
{
    /**
     * @throws InvalidSchedulerException
     */
    public function build(Rule $rule, SchedulerInterface $scheduler): Rule
    {
        try {
            $rule->setFreq('SECONDLY');
        } catch (InvalidArgument $e) {
            throw new InvalidSchedulerException();
        }

        return $rule;
    }
}
