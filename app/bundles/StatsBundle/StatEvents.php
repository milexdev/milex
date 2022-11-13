<?php

namespace Milex\StatsBundle;

final class StatEvents
{
    /**
     * The milex.aggregate_stat_request event is dispatched when an aggregate stat is requested.
     *
     * The event listener receives a \Milex\StatsBundle\Event\AggregateStatRequestEvent instance.
     *
     * @var string
     */
    const AGGREGATE_STAT_REQUEST = 'milex.aggregate_stat_request';
}
