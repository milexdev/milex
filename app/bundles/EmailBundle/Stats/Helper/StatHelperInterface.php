<?php

namespace Milex\EmailBundle\Stats\Helper;

use Milex\EmailBundle\Stats\FetchOptions\EmailStatOptions;
use Milex\StatsBundle\Aggregate\Collection\StatCollection;

interface StatHelperInterface
{
    /**
     * @return string
     */
    public function getName();

    public function fetchStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options);

    public function generateStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options, StatCollection $statCollection);
}
