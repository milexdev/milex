<?php

namespace Milex\StatsBundle\Aggregate;

use Milex\StatsBundle\Aggregate\Collection\StatCollection;
use Milex\StatsBundle\Event\AggregateStatRequestEvent;
use Milex\StatsBundle\Event\Options\FetchOptions;
use Milex\StatsBundle\StatEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Collector.
 */
class Collector
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Collector constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $statName
     *
     * @return StatCollection
     */
    public function fetchStats($statName, \DateTime $fromDateTime, \DateTime $toDateTime, FetchOptions $fetchOptions = null)
    {
        if (null === $fetchOptions) {
            $fetchOptions = new FetchOptions();
        }

        $event = new AggregateStatRequestEvent($statName, $fromDateTime, $toDateTime, $fetchOptions);

        $this->eventDispatcher->dispatch(StatEvents::AGGREGATE_STAT_REQUEST, $event);

        return $event->getStatCollection();
    }
}
