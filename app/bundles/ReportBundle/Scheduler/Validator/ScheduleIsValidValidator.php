<?php

namespace Milex\ReportBundle\Scheduler\Validator;

use Milex\ReportBundle\Entity\Report;
use Milex\ReportBundle\Scheduler\Builder\SchedulerBuilder;
use Milex\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Milex\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use Milex\ReportBundle\Scheduler\Exception\ScheduleNotValidException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ScheduleIsValidValidator extends ConstraintValidator
{
    /**
     * @var SchedulerBuilder
     */
    private $schedulerBuilder;

    public function __construct(SchedulerBuilder $schedulerBuilder)
    {
        $this->schedulerBuilder = $schedulerBuilder;
    }

    /**
     * @param Report $report
     */
    public function validate($report, Constraint $constraint)
    {
        if (!$report->isScheduled()) {
            $report->setAsNotScheduled();

            return;
        }

        if (is_null($report->getToAddress())) {
            $this->context->buildViolation('milex.report.schedule.to_address_required')
                ->atPath('toAddress')
                ->addViolation();
        }

        if ($report->isScheduledDaily()) {
            $report->ensureIsDailyScheduled();
            $this->buildScheduler($report);

            return;
        }
        if ($report->isScheduledWeekly()) {
            try {
                $report->ensureIsWeeklyScheduled();
                $this->buildScheduler($report);

                return;
            } catch (ScheduleNotValidException $e) {
                $this->addReportScheduleNotValidViolation();
            }
        }
        if ($report->isScheduledMonthly()) {
            try {
                $report->ensureIsMonthlyScheduled();
                $this->buildScheduler($report);

                return;
            } catch (ScheduleNotValidException $e) {
                $this->addReportScheduleNotValidViolation();
            }
        }
    }

    private function addReportScheduleNotValidViolation()
    {
        $this->context->buildViolation('milex.report.schedule.notValid')
            ->atPath('isScheduled')
            ->addViolation();
    }

    private function buildScheduler(Report $report)
    {
        try {
            $this->schedulerBuilder->getNextEvent($report);

            return;
        } catch (InvalidSchedulerException $e) {
            $message = 'milex.report.schedule.notValid';
        } catch (NotSupportedScheduleTypeException $e) {
            $message = 'milex.report.schedule.notSupportedType';
        }

        $this->context->buildViolation($message)
            ->atPath('isScheduled')
            ->addViolation();
    }
}
