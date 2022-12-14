<?php

namespace Milex\ReportBundle\Scheduler\Model;

use Doctrine\ORM\EntityManager;
use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Entity\Scheduler;
use Milex\ReportBundle\Entity\SchedulerRepository;
use Milex\ReportBundle\Scheduler\Date\DateBuilder;
use Milex\ReportBundle\Scheduler\Exception\NoScheduleException;

class SchedulerPlanner
{
    /**
     * @var DateBuilder
     */
    private $dateBuilder;

    /**
     * @var SchedulerRepository
     */
    private $schedulerRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(DateBuilder $dateBuilder, EntityManager $entityManager)
    {
        $this->dateBuilder         = $dateBuilder;
        $this->entityManager       = $entityManager;
        $this->schedulerRepository = $entityManager->getRepository(Scheduler::class);
    }

    public function computeScheduler(Report $report)
    {
        $this->removeSchedulerOfReport($report);
        $this->planScheduler($report);
    }

    private function planScheduler(Report $report)
    {
        try {
            $date = $this->dateBuilder->getNextEvent($report);
        } catch (NoScheduleException $e) {
            return;
        }

        $scheduler = new Scheduler($report, $date);
        $this->entityManager->persist($scheduler);
        $this->entityManager->flush();
    }

    private function removeSchedulerOfReport(Report $report)
    {
        $scheduler = $this->schedulerRepository->getSchedulerByReport($report);
        if (!$scheduler) {
            return;
        }

        $this->entityManager->remove($scheduler);
        $this->entityManager->flush();
    }
}
