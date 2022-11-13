<?php

namespace Milex\ReportBundle\Scheduler;

use Recurr\Rule;

interface BuilderInterface
{
    public function build(Rule $rule, SchedulerInterface $scheduler);
}
