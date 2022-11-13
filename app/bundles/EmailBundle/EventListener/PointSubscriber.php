<?php

namespace Milex\EmailBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event\EmailOpenEvent;
use Milex\EmailBundle\Event\EmailSendEvent;
use Milex\EmailBundle\Form\Type\EmailOpenType;
use Milex\EmailBundle\Form\Type\EmailSendType;
use Milex\EmailBundle\Form\Type\EmailToUserType;
use Milex\LeadBundle\Entity\Lead;
use Milex\PointBundle\Event\PointBuilderEvent;
use Milex\PointBundle\Event\TriggerBuilderEvent;
use Milex\PointBundle\Model\PointModel;
use Milex\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PointSubscriber implements EventSubscriberInterface
{
    /**
     * @var PointModel
     */
    private $pointModel;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(PointModel $pointModel, EntityManager $entityManager)
    {
        $this->pointModel    = $pointModel;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            PointEvents::POINT_ON_BUILD   => ['onPointBuild', 0],
            PointEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
            EmailEvents::EMAIL_ON_OPEN    => ['onEmailOpen', 0],
            EmailEvents::EMAIL_ON_SEND    => ['onEmailSend', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event)
    {
        $action = [
            'group'    => 'milex.email.actions',
            'label'    => 'milex.email.point.action.open',
            'callback' => ['\\Milex\\EmailBundle\\Helper\\PointEventHelper', 'validateEmail'],
            'formType' => EmailOpenType::class,
        ];

        $event->addAction('email.open', $action);

        $action = [
            'group'    => 'milex.email.actions',
            'label'    => 'milex.email.point.action.send',
            'callback' => ['\\Milex\\EmailBundle\\Helper\\PointEventHelper', 'validateEmail'],
            'formType' => EmailOpenType::class,
        ];

        $event->addAction('email.send', $action);
    }

    public function onTriggerBuild(TriggerBuilderEvent $event)
    {
        $sendEvent = [
            'group'           => 'milex.email.point.trigger',
            'label'           => 'milex.email.point.trigger.sendemail',
            'callback'        => ['\\Milex\\EmailBundle\\Helper\\PointEventHelper', 'sendEmail'],
            'formType'        => EmailSendType::class,
            'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_email'],
            'formTheme'       => 'MilexEmailBundle:FormTheme\EmailSendList',
        ];

        $event->addEvent('email.send', $sendEvent);

        $sendToOwnerEvent = [
          'group'           => 'milex.email.point.trigger',
          'label'           => 'milex.email.point.trigger.send_email_to_user',
          'formType'        => EmailToUserType::class,
          'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_email'],
          'formTheme'       => 'MilexEmailBundle:FormTheme\EmailSendList',
          'eventName'       => EmailEvents::ON_SENT_EMAIL_TO_USER,
        ];

        $event->addEvent('email.send_to_user', $sendToOwnerEvent);
    }

    /**
     * Trigger point actions for email open.
     */
    public function onEmailOpen(EmailOpenEvent $event)
    {
        $this->pointModel->triggerAction('email.open', $event->getEmail());
    }

    /**
     * Trigger point actions for email send.
     */
    public function onEmailSend(EmailSendEvent $event)
    {
        $leadArray = $event->getLead();
        if ($leadArray && is_array($leadArray) && !empty($leadArray['id'])) {
            $lead = $this->entityManager->getReference(Lead::class, $leadArray['id']);
        } else {
            return;
        }

        $this->pointModel->triggerAction('email.send', $event->getEmail(), null, $lead, true);
    }
}
