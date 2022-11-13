<?php

namespace Milex\FormBundle\EventListener;

use Milex\FormBundle\Event\SubmissionEvent;
use Milex\FormBundle\FormEvents;
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
            WebhookEvents::WEBHOOK_ON_BUILD => ['onWebhookBuild', 0],
            FormEvents::FORM_ON_SUBMIT      => ['onFormSubmit', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event)
    {
        // add checkbox to the webhook form for new leads
        $formSubmit = [
            'label'       => 'milex.form.webhook.event.form.submit',
            'description' => 'milex.form.webhook.event.form.submit_desc',
        ];

        // add it to the list
        $event->addEvent(FormEvents::FORM_ON_SUBMIT, $formSubmit);
    }

    public function onFormSubmit(SubmissionEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            FormEvents::FORM_ON_SUBMIT,
            [
                'submission' => $event->getSubmission(),
            ],
            [
                'submissionDetails',
                'ipAddress',
                'leadList',
                'pageList',
                'formList',
            ]
        );
    }
}
