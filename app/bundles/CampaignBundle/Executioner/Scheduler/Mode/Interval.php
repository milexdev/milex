<?php

namespace Milex\CampaignBundle\Executioner\Scheduler\Mode;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CampaignBundle\Entity\Event;
use Milex\CampaignBundle\Entity\LeadEventLog;
use Milex\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException;
use Milex\CampaignBundle\Executioner\Scheduler\Mode\DAO\GroupExecutionDateDAO;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\LeadBundle\Entity\Lead;
use Psr\Log\LoggerInterface;

class Interval implements ScheduleModeInterface
{
    const LOG_DATE_FORMAT = 'Y-m-d H:i:s T';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var \DateTimeZone
     */
    private $defaultTimezone;

    /**
     * Interval constructor.
     */
    public function __construct(LoggerInterface $logger, CoreParametersHelper $coreParametersHelper)
    {
        $this->logger               = $logger;
        $this->coreParametersHelper = $coreParametersHelper;
    }

    /**
     * @return \DateTime
     *
     * @throws NotSchedulableException
     */
    public function getExecutionDateTime(Event $event, \DateTime $compareFromDateTime, \DateTime $comparedToDateTime)
    {
        $interval = $event->getTriggerInterval();
        $unit     = $event->getTriggerIntervalUnit();

        try {
            $this->logger->debug(
                'CAMPAIGN: ('.$event->getId().') Adding interval of '.$interval.$unit.' to '.$comparedToDateTime->format(self::LOG_DATE_FORMAT)
            );
            $comparedToDateTime->add((new DateTimeHelper())->buildInterval($interval, $unit));
        } catch (\Exception $exception) {
            $this->logger->error('CAMPAIGN: Determining interval scheduled failed with "'.$exception->getMessage().'"');

            throw new NotSchedulableException();
        }

        if ($comparedToDateTime > $compareFromDateTime) {
            $this->logger->debug(
                'CAMPAIGN: ('.$event->getId().') '.$comparedToDateTime->format(self::LOG_DATE_FORMAT).' is later than '
                .$compareFromDateTime->format(self::LOG_DATE_FORMAT).' and thus returning '.$comparedToDateTime->format(self::LOG_DATE_FORMAT)
            );

            //the event is to be scheduled based on the time interval
            return $comparedToDateTime;
        }

        $this->logger->debug(
            'CAMPAIGN: ('.$event->getId().') '.$comparedToDateTime->format(self::LOG_DATE_FORMAT).' is earlier than '
            .$compareFromDateTime->format(self::LOG_DATE_FORMAT).' and thus returning '.$compareFromDateTime->format(self::LOG_DATE_FORMAT)
        );

        return $compareFromDateTime;
    }

    /**
     * @return \DateTime
     *
     * @throws NotSchedulableException
     */
    public function validateExecutionDateTime(LeadEventLog $log, \DateTime $compareFromDateTime)
    {
        $event         = $log->getEvent();
        $dateTriggered = clone $log->getDateTriggered();

        if (!$this->isContactSpecificExecutionDateRequired($event)) {
            return $this->getExecutionDateTime($event, $compareFromDateTime, $dateTriggered);
        }

        $interval      = $event->getTriggerInterval();
        $unit          = $event->getTriggerIntervalUnit();

        if ($interval && $unit) {
            $dateTriggered->add((new DateTimeHelper())->buildInterval($interval, $unit));
        }

        if ($dateTriggered < $compareFromDateTime) {
            $this->logger->debug(
                sprintf('CAMPAIGN: (%s) %s is earlier than %s and thus setting %s', $event->getId(), $dateTriggered->format(self::LOG_DATE_FORMAT), $compareFromDateTime->format(self::LOG_DATE_FORMAT), $compareFromDateTime->format(self::LOG_DATE_FORMAT))
            );
            $dateTriggered = clone $compareFromDateTime;
        }

        $hour      = $event->getTriggerHour();
        $startTime = $event->getTriggerRestrictedStartHour();
        $endTime   = $event->getTriggerRestrictedStopHour();
        $dow       = $event->getTriggerRestrictedDaysOfWeek();

        return $this->getGroupExecutionDateTime($event->getId(), $log->getLead(), $dateTriggered, $hour, $startTime, $endTime, $dow);
    }

    /**
     * @return GroupExecutionDateDAO[]
     */
    public function groupContactsByDate(Event $event, ArrayCollection $contacts, \DateTime $executionDate, \DateTime $compareFromDateTime = null)
    {
        $groupedExecutionDates = [];
        $hour                  = $event->getTriggerHour();
        $startTime             = $event->getTriggerRestrictedStartHour();
        $endTime               = $event->getTriggerRestrictedStopHour();
        $daysOfWeek            = $event->getTriggerRestrictedDaysOfWeek();

        /** @var Lead $contact */
        foreach ($contacts as $contact) {
            $groupExecutionDate = $this->getGroupExecutionDateTime(
                $event->getId(),
                $contact,
                $executionDate,
                $hour,
                $startTime,
                $endTime,
                $daysOfWeek
            );
            if (!isset($groupedExecutionDates[$groupExecutionDate->getTimestamp()])) {
                $groupedExecutionDates[$groupExecutionDate->getTimestamp()] = new GroupExecutionDateDAO($groupExecutionDate);
            }

            $groupedExecutionDates[$groupExecutionDate->getTimestamp()]->addContact($contact);
        }

        return $groupedExecutionDates;
    }

    /**
     * Checks if an event has a relative time configured.
     *
     * @return bool
     */
    public function isContactSpecificExecutionDateRequired(Event $event)
    {
        if (Event::TRIGGER_MODE_INTERVAL !== $event->getTriggerMode()) {
            return false;
        }

        // Restrict just for daily scheduling
        if (!in_array($event->getTriggerIntervalUnit(), ['d', 'm', 'y'])) {
            return false;
        }

        if (
            null === $event->getTriggerHour() &&
            (null === $event->getTriggerRestrictedStartHour() || null === $event->getTriggerRestrictedStopHour()) &&
            empty($event->getTriggerRestrictedDaysOfWeek())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $eventId
     *
     * @return \DateTime
     */
    private function getGroupExecutionDateTime(
        $eventId,
        Lead $contact,
        \DateTime $compareFromDateTime,
        \DateTime $hour = null,
        \DateTime $startTime = null,
        \DateTime $endTime = null,
        array $daysOfWeek = []
    ) {
        $this->logger->debug(
            sprintf('CAMPAIGN: Comparing calculated executed time for event ID %s and contact ID %s with %s', $eventId, $contact->getId(), $compareFromDateTime->format('Y-m-d H:i:s e'))
        );

        if ($hour) {
            $this->logger->debug(
                sprintf('CAMPAIGN: Scheduling event ID %s for contact ID %s based on hour of %s', $eventId, $contact->getId(), $hour->format('H:i e'))
            );
            $groupDateTime = $this->getExecutionDateTimeFromHour($contact, $hour, $eventId, $compareFromDateTime);
        } elseif ($startTime && $endTime) {
            $this->logger->debug(
                sprintf(
                    'CAMPAIGN: Scheduling event ID %s for contact ID %s based on hour range of %s to %s',
                    $eventId,
                    $contact->getId(),
                    $startTime->format('H:i e'),
                    $endTime->format('H:i e')
                )
            );

            $groupDateTime = $this->getExecutionDateTimeBetweenHours($contact, $startTime, $endTime, $eventId, $compareFromDateTime);
        } else {
            $this->logger->debug(
                sprintf('CAMPAIGN: Scheduling event ID %s for contact ID %s without hour restrictions.', $eventId, $contact->getId())
            );

            $groupDateTime = clone $compareFromDateTime;
        }

        if ($daysOfWeek) {
            $this->logger->debug(
                sprintf(
                    'CAMPAIGN: Scheduling event ID %s for contact ID %s based on DOW restrictions of %s',
                    $eventId,
                    $contact->getId(),
                    implode(',', $daysOfWeek)
                )
            );

            // Schedule for the next day of the week if applicable
            while (!in_array((int) $groupDateTime->format('w'), $daysOfWeek)) {
                $groupDateTime->modify('+1 day');
            }
        }

        return $groupDateTime;
    }

    /**
     * @param $eventId
     *
     * @return \DateTime
     */
    private function getExecutionDateTimeFromHour(Lead $contact, \DateTime $hour, $eventId, \DateTime $compareFromDateTime)
    {
        $groupHour = clone $hour;

        // Set execution to UTC
        if ($timezone = $contact->getTimezone()) {
            try {
                // Set the group's timezone to the contact's
                $contactTimezone = new \DateTimeZone($timezone);

                $this->logger->debug(
                    'CAMPAIGN: ('.$eventId.') Setting '.$timezone.' for contact '.$contact->getId()
                );

                // Get now in the contacts timezone then add the number of days from now and the original execution date
                $groupExecutionDate = clone $compareFromDateTime;
                $groupExecutionDate->setTimezone($contactTimezone);

                $groupExecutionDate->setTime($groupHour->format('H'), $groupHour->format('i'));

                return $groupExecutionDate;
            } catch (\Exception $exception) {
                // Timezone is not recognized so use the default
                $this->logger->debug(
                    'CAMPAIGN: ('.$eventId.') '.$timezone.' for contact '.$contact->getId().' is not recognized'
                );
            }
        }

        $groupExecutionDate = clone $compareFromDateTime;
        $groupExecutionDate->setTimezone($this->getDefaultTimezone());

        $groupExecutionDate->setTime($groupHour->format('H'), $groupHour->format('i'));

        return $groupExecutionDate;
    }

    /**
     * @param $eventId
     *
     * @return \DateTime
     */
    private function getExecutionDateTimeBetweenHours(
        Lead $contact,
        \DateTime $startTime,
        \DateTime $endTime,
        $eventId,
        \DateTime $compareFromDateTime
    ) {
        $startTime = clone $startTime;
        $endTime   = clone $endTime;

        if ($endTime < $startTime) {
            // End time is after start time so switch them
            $tempStartTime = clone $startTime;
            $startTime     = clone $endTime;
            $endTime       = clone $tempStartTime;
            unset($tempStartTime);
        }

        // Set execution to UTC
        if ($timezone = $contact->getTimezone()) {
            try {
                // Set the group's timezone to the contact's
                $contactTimezone = new \DateTimeZone($timezone);

                $this->logger->debug(
                    'CAMPAIGN: ('.$eventId.') Setting '.$timezone.' for contact '.$contact->getId()
                );

                // Get now in the contacts timezone then add the number of days from now and the original execution date
                $groupExecutionDate = clone $compareFromDateTime;
                $groupExecutionDate->setTimezone($contactTimezone);
            } catch (\Exception $exception) {
                // Timezone is not recognized so use the default
                $this->logger->debug(
                    'CAMPAIGN: ('.$eventId.') '.$timezone.' for contact '.$contact->getId().' is not recognized'
                );
            }
        }

        if (!isset($groupExecutionDate)) {
            $groupExecutionDate = clone $compareFromDateTime;
            $groupExecutionDate->setTimezone($this->getDefaultTimezone());
        }

        // Is the time between the start and end hours?
        $testStartDateTime = clone $groupExecutionDate;
        $testStartDateTime->setTime($startTime->format('H'), $startTime->format('i'));

        $testStopDateTime = clone $groupExecutionDate;
        $testStopDateTime->setTime($endTime->format('H'), $endTime->format('i'));

        if ($groupExecutionDate < $testStartDateTime) {
            // Too early so set it to the start date
            return $testStartDateTime;
        }

        if ($groupExecutionDate >= $testStopDateTime) {
            // Too late so try again tomorrow
            $groupExecutionDate->modify('+1 day')->setTime($startTime->format('H'), $startTime->format('i'));
        }

        return $groupExecutionDate;
    }

    /**
     * @return \DateTimeZone
     */
    private function getDefaultTimezone()
    {
        if ($this->defaultTimezone) {
            return $this->defaultTimezone;
        }

        $this->defaultTimezone = new \DateTimeZone(
            $this->coreParametersHelper->get('default_timezone', 'UTC')
        );

        return $this->defaultTimezone;
    }
}