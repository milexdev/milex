<?php

namespace Milex\PointBundle\Model;

use Milex\CoreBundle\Factory\MilexFactory;
use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Model\FormModel as CommonFormModel;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\PointBundle\Entity\LeadTriggerLog;
use Milex\PointBundle\Entity\Trigger;
use Milex\PointBundle\Entity\TriggerEvent;
use Milex\PointBundle\Event as Events;
use Milex\PointBundle\Form\Type\TriggerType;
use Milex\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class TriggerModel extends CommonFormModel
{
    protected $triggers = [];

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var TriggerEventModel
     */
    protected $pointTriggerEventModel;

    /**
     * @deprecated https://github.com/milex/milex/issues/8229
     *
     * @var MilexFactory
     */
    protected $milexFactory;

    /**
     * @var ContactTracker
     */
    private $contactTracker;

    public function __construct(
        IpLookupHelper $ipLookupHelper,
        LeadModel $leadModel,
        TriggerEventModel $pointTriggerEventModel,
        MilexFactory $milexFactory,
        ContactTracker $contactTracker
    ) {
        $this->ipLookupHelper         = $ipLookupHelper;
        $this->leadModel              = $leadModel;
        $this->pointTriggerEventModel = $pointTriggerEventModel;
        $this->milexFactory          = $milexFactory;
        $this->contactTracker         = $contactTracker;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Milex\PointBundle\Entity\TriggerRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository(Trigger::class);
    }

    /**
     * Retrieves an instance of the TriggerEventRepository.
     *
     * @return \Milex\PointBundle\Entity\TriggerEventRepository
     */
    public function getEventRepository()
    {
        return $this->em->getRepository(TriggerEvent::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'point:triggers';
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Trigger) {
            throw new MethodNotAllowedHttpException(['Trigger']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(TriggerType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Milex\PointBundle\Entity\Trigger $entity
     * @param bool                               $unlock
     */
    public function saveEntity($entity, $unlock = true)
    {
        $isNew = ($entity->getId()) ? false : true;

        parent::saveEntity($entity, $unlock);

        //should we trigger for existing leads?
        if ($entity->getTriggerExistingLeads() && $entity->isPublished()) {
            $events    = $entity->getEvents();
            $repo      = $this->getEventRepository();
            $persist   = [];
            $ipAddress = $this->ipLookupHelper->getIpAddress();

            foreach ($events as $event) {
                $filter = ['force' => [
                    [
                        'column' => 'l.date_added',
                        'expr'   => 'lte',
                        'value'  => (new DateTimeHelper($entity->getDateAdded()))->toUtcString(),
                    ],
                    [
                        'column' => 'l.points',
                        'expr'   => 'gte',
                        'value'  => $entity->getPoints(),
                    ],
                ]];

                if (!$isNew) {
                    //get a list of leads that has already had this event applied
                    $leadIds = $repo->getLeadsForEvent($event->getId());
                    if (!empty($leadIds)) {
                        $filter['force'][] = [
                            'column' => 'l.id',
                            'expr'   => 'notIn',
                            'value'  => $leadIds,
                        ];
                    }
                }

                //get a list of leads that are before the trigger's date_added and trigger if not already done so
                $leads = $this->leadModel->getEntities([
                    'filter' => $filter,
                ]);

                foreach ($leads as $l) {
                    if ($this->triggerEvent($event->convertToArray(), $l, true)) {
                        $log = new LeadTriggerLog();
                        $log->setIpAddress($ipAddress);
                        $log->setEvent($event);
                        $log->setLead($l);
                        $log->setDateFired(new \DateTime());
                        $event->addLog($log);
                        $persist[] = $event;
                    }
                }
            }

            if (!empty($persist)) {
                $repo->saveEntities($persist);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Trigger|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new Trigger();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Trigger) {
            throw new MethodNotAllowedHttpException(['Trigger']);
        }

        switch ($action) {
            case 'pre_save':
                $name = PointEvents::TRIGGER_PRE_SAVE;
                break;
            case 'post_save':
                $name = PointEvents::TRIGGER_POST_SAVE;
                break;
            case 'pre_delete':
                $name = PointEvents::TRIGGER_PRE_DELETE;
                break;
            case 'post_delete':
                $name = PointEvents::TRIGGER_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new Events\TriggerEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }

    /**
     * @param array $sessionEvents
     */
    public function setEvents(Trigger $entity, $sessionEvents)
    {
        $order           = 1;
        $existingActions = $entity->getEvents();

        foreach ($sessionEvents as $properties) {
            $isNew = (!empty($properties['id']) && isset($existingActions[$properties['id']])) ? false : true;
            $event = !$isNew ? $existingActions[$properties['id']] : new TriggerEvent();

            foreach ($properties as $f => $v) {
                if (in_array($f, ['id', 'order'])) {
                    continue;
                }

                $func = 'set'.ucfirst($f);
                if (method_exists($event, $func)) {
                    $event->$func($v);
                }
            }
            $event->setTrigger($entity);
            $event->setOrder($order);
            ++$order;
            $entity->addTriggerEvent($properties['id'], $event);
        }

        // Persist if editing the trigger
        if ($entity->getId()) {
            $this->pointTriggerEventModel->saveEntities($entity->getEvents());
        }
    }

    /**
     * Gets array of custom events from bundles subscribed PointEvents::TRIGGER_ON_BUILD.
     *
     * @return mixed[]
     */
    public function getEvents()
    {
        static $events;

        if (empty($events)) {
            //build them
            $events = [];
            $event  = new Events\TriggerBuilderEvent($this->translator);
            $this->dispatcher->dispatch(PointEvents::TRIGGER_ON_BUILD, $event);
            $events = $event->getEvents();
        }

        return $events;
    }

    /**
     * Gets array of custom events from bundles inside groups.
     *
     * @return mixed
     */
    public function getEventGroups()
    {
        $events = $this->getEvents();
        $groups = [];
        foreach ($events as $key => $event) {
            $groups[$event['group']][$key] = $event;
        }

        return $groups;
    }

    /**
     * Triggers a specific event.
     *
     * @param array $event triggerEvent converted to array
     * @param bool  $force
     *
     * @return bool Was event triggered
     */
    public function triggerEvent($event, Lead $lead = null, $force = false)
    {
        //only trigger events for anonymous users
        if (!$force && !$this->security->isAnonymous()) {
            return false;
        }

        if (null === $lead) {
            $lead = $this->contactTracker->getContact();
        }

        if (!$force) {
            //get a list of events that has already been performed on this lead
            $appliedEvents = $this->getEventRepository()->getLeadTriggeredEvents($lead->getId());

            //if it's already been done, then skip it
            if (isset($appliedEvents[$event['id']])) {
                return false;
            }
        }

        $availableEvents = $this->getEvents();
        $eventType       = $event['type'];

        //make sure the event still exists
        if (!isset($availableEvents[$eventType])) {
            return false;
        }

        $settings = $availableEvents[$eventType];

        if (isset($settings['callback']) && is_callable($settings['callback'])) {
            return $this->invokeCallback($event, $lead, $settings);
        } else {
            /** @var TriggerEvent $triggerEvent */
            $triggerEvent = $this->getEventRepository()->find($event['id']);

            $triggerExecutedEvent = new Events\TriggerExecutedEvent($triggerEvent, $lead);

            $this->dispatcher->dispatch($settings['eventName'], $triggerExecutedEvent);

            return $triggerExecutedEvent->getResult();
        }
    }

    /**
     * @param $event
     *
     * @return bool
     */
    private function invokeCallback($event, Lead $lead, array $settings)
    {
        $args = [
          'event'   => $event,
          'lead'    => $lead,
          'factory' => $this->milexFactory,
          'config'  => $event['properties'],
        ];

        if (is_array($settings['callback'])) {
            $reflection = new \ReflectionMethod($settings['callback'][0], $settings['callback'][1]);
        } elseif (false !== strpos($settings['callback'], '::')) {
            $parts      = explode('::', $settings['callback']);
            $reflection = new \ReflectionMethod($parts[0], $parts[1]);
        } else {
            $reflection = new \ReflectionMethod(null, $settings['callback']);
        }

        $pass = [];
        foreach ($reflection->getParameters() as $param) {
            if (isset($args[$param->getName()])) {
                $pass[] = $args[$param->getName()];
            } else {
                $pass[] = null;
            }
        }

        return $reflection->invokeArgs($this, $pass);
    }

    /**
     * Trigger events for the current lead.
     */
    public function triggerEvents(Lead $lead)
    {
        $points = $lead->getPoints();

        //find all published triggers that is applicable to this points
        /** @var \Milex\PointBundle\Entity\TriggerEventRepository $repo */
        $repo   = $this->getEventRepository();
        $events = $repo->getPublishedByPointTotal($points);

        if (!empty($events)) {
            //get a list of actions that has already been applied to this lead
            $appliedEvents = $repo->getLeadTriggeredEvents($lead->getId());
            $ipAddress     = $this->ipLookupHelper->getIpAddress();
            $persist       = [];
            foreach ($events as $event) {
                if (isset($appliedEvents[$event['id']])) {
                    //don't apply the event to the lead if it's already been done
                    continue;
                }

                if ($this->triggerEvent($event, $lead, true)) {
                    $log = new LeadTriggerLog();
                    $log->setIpAddress($ipAddress);
                    $log->setEvent($this->em->getReference('MilexPointBundle:TriggerEvent', $event['id']));
                    $log->setLead($lead);
                    $log->setDateFired(new \DateTime());
                    $persist[] = $log;
                }
            }

            if (!empty($persist)) {
                $this->getEventRepository()->saveEntities($persist);

                $this->em->clear('Milex\PointBundle\Entity\LeadTriggerLog');
                $this->em->clear('Milex\PointBundle\Entity\TriggerEvent');
            }
        }
    }

    /**
     * Returns configured color based on passed in $points.
     *
     * @param $points
     *
     * @return string
     */
    public function getColorForLeadPoints($points)
    {
        if (!$this->triggers) {
            $this->triggers = $this->getRepository()->getTriggerColors();
        }

        foreach ($this->triggers as $trigger) {
            if ($points >= $trigger['points']) {
                return $trigger['color'];
            }
        }

        return '';
    }
}
