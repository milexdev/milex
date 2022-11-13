<?php

namespace Milex\CampaignBundle\Executioner\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CampaignBundle\Entity\Event;
use Milex\CampaignBundle\Entity\LeadEventLog;
use Milex\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use Milex\CampaignBundle\EventCollector\Accessor\Event\ConditionAccessor;
use Milex\CampaignBundle\Executioner\Dispatcher\ConditionDispatcher;
use Milex\CampaignBundle\Executioner\Exception\CannotProcessEventException;
use Milex\CampaignBundle\Executioner\Exception\ConditionFailedException;
use Milex\CampaignBundle\Executioner\Result\EvaluatedContacts;

class ConditionExecutioner implements EventInterface
{
    const TYPE = 'condition';

    /**
     * @var ConditionDispatcher
     */
    private $dispatcher;

    /**
     * ConditionExecutioner constructor.
     */
    public function __construct(ConditionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return EvaluatedContacts
     *
     * @throws CannotProcessEventException
     */
    public function execute(AbstractEventAccessor $config, ArrayCollection $logs)
    {
        $evaluatedContacts = new EvaluatedContacts();

        /** @var LeadEventLog $log */
        foreach ($logs as $log) {
            try {
                /* @var ConditionAccessor $config */
                $this->dispatchEvent($config, $log);
                $evaluatedContacts->pass($log->getLead());
            } catch (ConditionFailedException $exception) {
                $evaluatedContacts->fail($log->getLead());
                $log->setNonActionPathTaken(true);
            }

            // Unschedule the condition and update date triggered timestamp
            $log->setDateTriggered(new \DateTime());
        }

        return $evaluatedContacts;
    }

    /**
     * @throws CannotProcessEventException
     * @throws ConditionFailedException
     */
    private function dispatchEvent(ConditionAccessor $config, LeadEventLog $log)
    {
        if (Event::TYPE_CONDITION !== $log->getEvent()->getEventType()) {
            throw new CannotProcessEventException('Cannot process event ID '.$log->getEvent()->getId().' as a condition.');
        }

        $conditionEvent = $this->dispatcher->dispatchEvent($config, $log);

        if (!$conditionEvent->wasConditionSatisfied()) {
            throw new ConditionFailedException('evaluation failed');
        }
    }
}
