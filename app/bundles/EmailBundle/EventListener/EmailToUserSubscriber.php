<?php

namespace Milex\EmailBundle\EventListener;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Exception\EmailCouldNotBeSentException;
use Milex\EmailBundle\Model\SendEmailToUser;
use Milex\PointBundle\Event\TriggerExecutedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailToUserSubscriber implements EventSubscriberInterface
{
    /** @var SendEmailToUser */
    private $sendEmailToUser;

    public function __construct(SendEmailToUser $sendEmailToUser)
    {
        $this->sendEmailToUser = $sendEmailToUser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [EmailEvents::ON_SENT_EMAIL_TO_USER => ['onEmailToUser', 0]];
    }

    public function onEmailToUser(TriggerExecutedEvent $event)
    {
        $triggerEvent = $event->getTriggerEvent();
        $config       = $triggerEvent->getProperties();
        $lead         = $event->getLead();

        try {
            $this->sendEmailToUser->sendEmailToUsers($config, $lead);
            $event->setSucceded();
        } catch (EmailCouldNotBeSentException $e) {
            $event->setFailed();
        }

        return $event;
    }
}
