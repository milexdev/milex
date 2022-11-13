<?php

namespace Milex\EmailBundle\EventListener;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event\EmailOpenEvent;
use Milex\EmailBundle\Event\EmailSendEvent;
use Milex\WebhookBundle\Event\WebhookBuilderEvent;
use Milex\WebhookBundle\Model\WebhookModel;
use Milex\WebhookBundle\WebhookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebhookSubscriber implements EventSubscriberInterface
{
    /**
     * @var WebhookModel
     */
    private $webhookModel;

    public function __construct(WebhookModel $webhookModel)
    {
        $this->webhookModel = $webhookModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_SEND      => ['onEmailSend', 0],
            EmailEvents::EMAIL_ON_OPEN      => ['onEmailOpen', 0],
            WebhookEvents::WEBHOOK_ON_BUILD => ['onWebhookBuild', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event)
    {
        // add checkbox to the webhook form for new leads
        $mailSend= [
            'label'       => 'milex.email.webhook.event.send',
            'description' => 'milex.email.webhook.event.send_desc',
        ];
        $mailOpen = [
            'label'       => 'milex.email.webhook.event.open',
            'description' => 'milex.email.webhook.event.open_desc',
        ];

        // add it to the list
        $event->addEvent(EmailEvents::EMAIL_ON_SEND, $mailSend);
        $event->addEvent(EmailEvents::EMAIL_ON_OPEN, $mailOpen);
    }

    public function onEmailSend(EmailSendEvent $event): void
    {
        // Ignore test email sends.
        if ($event->isInternalSend() || null === $event->getLead()) {
            return;
        }

        $this->webhookModel->queueWebhooksByType(
            EmailEvents::EMAIL_ON_SEND,
            [
                'email'       => $event->getEmail(),
                'contact'     => $event->getLead(),
                'tokens'      => $event->getTokens(),
                'contentHash' => $event->getContentHash(),
                'idHash'      => $event->getIdHash(),
                'content'     => $event->getContent(),
                'subject'     => $event->getSubject(),
                'source'      => $event->getSource(),
                'headers'     => $event->getTextHeaders(),
            ]
        );
    }

    public function onEmailOpen(EmailOpenEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            EmailEvents::EMAIL_ON_OPEN,
            [
                'stat' => $event->getStat(),
            ],
            [
                'statDetails',
                'leadList',
                'emailDetails',
            ]
        );
    }
}
