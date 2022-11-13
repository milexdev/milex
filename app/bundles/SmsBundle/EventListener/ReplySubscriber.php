<?php

namespace Milex\SmsBundle\EventListener;

use Milex\CoreBundle\Helper\InputHelper;
use Milex\LeadBundle\Entity\LeadEventLog;
use Milex\LeadBundle\Entity\LeadEventLogRepository;
use Milex\LeadBundle\Event\LeadTimelineEvent;
use Milex\LeadBundle\EventListener\TimelineEventLogTrait;
use Milex\LeadBundle\LeadEvents;
use Milex\SmsBundle\Event\ReplyEvent;
use Milex\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReplySubscriber implements EventSubscriberInterface
{
    use TimelineEventLogTrait;

    /**
     * ReplySubscriber constructor.
     */
    public function __construct(TranslatorInterface $translator, LeadEventLogRepository $eventLogRepository)
    {
        $this->translator         = $translator;
        $this->eventLogRepository = $eventLogRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SmsEvents::ON_REPLY              => ['onReply', 0],
            LeadEvents::TIMELINE_ON_GENERATE => 'onTimelineGenerate',
        ];
    }

    public function onReply(ReplyEvent $event)
    {
        $message = $event->getMessage();
        $contact = $event->getContact();

        $log = new LeadEventLog();
        $log
            ->setLead($contact)
            ->setBundle('sms')
            ->setObject('sms')
            ->setAction('reply')
            ->setProperties(
                [
                    'message' => InputHelper::clean($message),
                ]
            );

        $this->eventLogRepository->saveEntity($log);
        $this->eventLogRepository->detachEntity($log);
    }

    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $this->addEvents(
            $event,
            'sms_reply',
            'milex.sms.timeline.reply',
            'fa-mobile',
            'sms',
            'sms',
            'reply',
            'MilexSmsBundle:SubscribedEvents/Timeline:reply.html.php'
        );
    }
}
