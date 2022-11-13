<?php

namespace Milex\StatsBundle\Aggregate\Collection\Stats;

interface StatInterface
{
    /**
     * @return array
     */
    public function getStats();

    /**
     * @return int
     */
    public function getSum();

    /**
     * @return int
     */
    public function getCount();
}
