<?php

namespace Milex\CampaignBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Entity\Event;
use Milex\CampaignBundle\Entity\EventRepository;
use Milex\CampaignBundle\Entity\LeadRepository;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignEvent;
use Milex\CampaignBundle\Event\PendingEvent;
use Milex\CampaignBundle\Executioner\EventExecutioner;
use Milex\CampaignBundle\Form\Type\CampaignEventJumpToEventType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignActionJumpToEventSubscriber implements EventSubscriberInterface
{
    const EVENT_NAME = 'campaign.jump_to_event';

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventExecutioner
     */
    private $eventExecutioner;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    public function __construct(EventRepository $eventRepository, EventExecutioner $eventExecutioner, TranslatorInterface $translator, LeadRepository $leadRepository)
    {
        $this->eventRepository  = $eventRepository;
        $this->eventExecutioner = $eventExecutioner;
        $this->translator       = $translator;
        $this->leadRepository   = $leadRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_POST_SAVE     => ['processCampaignEventsAfterSave', 1],
            CampaignEvents::CAMPAIGN_ON_BUILD      => ['onCampaignBuild', 0],
            CampaignEvents::ON_EVENT_JUMP_TO_EVENT => ['onJumpToEvent', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        // Add action to jump to another event in the campaign flow.
        $event->addAction(self::EVENT_NAME, [
            'label'                  => 'milex.campaign.event.jump_to_event',
            'description'            => 'milex.campaign.event.jump_to_event_descr',
            'formType'               => CampaignEventJumpToEventType::class,
            'template'               => 'MilexCampaignBundle:Event:jump.html.php',
            'batchEventName'         => CampaignEvents::ON_EVENT_JUMP_TO_EVENT,
            'connectionRestrictions' => [
                'target' => [
                    Event::TYPE_DECISION  => ['none'],
                    Event::TYPE_ACTION    => ['none'],
                    Event::TYPE_CONDITION => ['none'],
                ],
            ],
        ]);
    }

    /**
     * Process campaign.jump_to_event actions.
     *
     * @throws \Milex\CampaignBundle\Executioner\Dispatcher\Exception\LogNotProcessedException
     * @throws \Milex\CampaignBundle\Executioner\Dispatcher\Exception\LogPassedAndFailedException
     * @throws \Milex\CampaignBundle\Executioner\Exception\CannotProcessEventException
     * @throws \Milex\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException
     */
    public function onJumpToEvent(PendingEvent $campaignEvent)
    {
        $event      = $campaignEvent->getEvent();
        $jumpTarget = $this->getJumpTargetForEvent($event, 'e.id');

        if (null === $jumpTarget) {
            // Target event has been removed.
            $pending  = $campaignEvent->getPending();
            $contacts = $campaignEvent->getContacts();
            foreach ($contacts as $logId => $contact) {
                // Pass with an error for the UI.
                $campaignEvent->passWithError(
                    $pending->get($logId),
                    $this->translator->trans('milex.campaign.campaign.jump_to_event.target_not_exist')
                );
            }
        } else {
            // Increment the campaign rotation for the given contacts and current campaign
            $this->leadRepository->incrementCampaignRotationForContacts(
                $campaignEvent->getContactsKeyedById()->getKeys(),
                $event->getCampaign()->getId()
            );
            $this->eventExecutioner->executeForContacts($jumpTarget, $campaignEvent->getContactsKeyedById());
            $campaignEvent->passRemaining();
        }
    }

    /**
     * Update campaign events.
     *
     * This block specifically handles the campaign.jump_to_event properties
     * to ensure that it has the actual ID and not the temp_id as the
     * target for the jump.
     */
    public function processCampaignEventsAfterSave(CampaignEvent $campaignEvent)
    {
        $campaign = $campaignEvent->getCampaign();
        $events   = $campaign->getEvents();
        $toSave   = [];

        foreach ($events as $event) {
            if (self::EVENT_NAME !== $event->getType()) {
                continue;
            }

            $jumpTarget = $this->getJumpTargetForEvent($event, 'e.tempId');

            if (null !== $jumpTarget) {
                $event->setProperties(array_merge(
                    $event->getProperties(),
                    [
                        'jumpToEvent' => $jumpTarget->getId(),
                    ]
                ));

                $toSave[] = $event;
            }
        }

        if (count($toSave)) {
            $this->eventRepository->saveEntities($toSave);
        }
    }

    /**
     * Inspect a jump event and get its target.
     */
    private function getJumpTargetForEvent(Event $event, string $column): ?Event
    {
        $properties  = $event->getProperties();
        $jumpToEvent = $this->eventRepository->getEntities([
            'ignore_paginator' => true,
            'filter'           => [
                'force' => [
                    [
                        'column' => $column,
                        'value'  => $properties['jumpToEvent'],
                        'expr'   => 'eq',
                    ],
                    [
                        'column' => 'e.campaign',
                        'value'  => $event->getCampaign(),
                        'expr'   => 'eq',
                    ],
                ],
            ],
        ]);

        if (count($jumpToEvent)) {
            return $jumpToEvent[0];
        }

        return null;
    }
}
