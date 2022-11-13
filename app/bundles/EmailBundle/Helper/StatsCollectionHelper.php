<?php

namespace Milex\EmailBundle\Helper;

use Milex\EmailBundle\Stats\FetchOptions\EmailStatOptions;
use Milex\EmailBundle\Stats\Helper\BouncedHelper;
use Milex\EmailBundle\Stats\Helper\ClickedHelper;
use Milex\EmailBundle\Stats\Helper\FailedHelper;
use Milex\EmailBundle\Stats\Helper\FilterTrait;
use Milex\EmailBundle\Stats\Helper\OpenedHelper;
use Milex\EmailBundle\Stats\Helper\SentHelper;
use Milex\EmailBundle\Stats\Helper\UnsubscribedHelper;
use Milex\EmailBundle\Stats\StatHelperContainer;
use Milex\StatsBundle\Aggregate\Collection\StatCollection;

class StatsCollectionHelper
{
    use FilterTrait;

    const GENERAL_STAT_PREFIX = 'email';

    /**
     * @var StatHelperContainer
     */
    private $helperContainer;

    /**
     * StatsCollectionHelper constructor.
     */
    public function __construct(StatHelperContainer $helperContainer)
    {
        $this->helperContainer = $helperContainer;
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchSentStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(SentHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchOpenedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(OpenedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchFailedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(FailedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchClickedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(ClickedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchBouncedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(BouncedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchUnsubscribedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(UnsubscribedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Generate stats from Milex's raw data.
     *
     * @param $statName
     *
     * @throws \Milex\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function generateStats(
        $statName,
        \DateTime $fromDateTime,
        \DateTime $toDateTime,
        EmailStatOptions $options,
        StatCollection $statCollection
    ) {
        $this->helperContainer->getHelper($statName)->generateStats($fromDateTime, $toDateTime, $options, $statCollection);
    }
}
