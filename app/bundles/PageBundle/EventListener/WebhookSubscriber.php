<?php

namespace Milex\PageBundle\EventListener;

use Milex\PageBundle\Event\PageHitEvent;
use Milex\PageBundle\PageEvents;
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
            PageEvents::PAGE_ON_HIT         => ['onPageHit', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event)
    {
        // add checkbox to the webhook form for new leads
        $pageHit = [
            'label'       => 'milex.page.webhook.event.hit',
            'description' => 'milex.page.webhook.event.hit_desc',
        ];

        // add it to the list
        $event->addEvent(PageEvents::PAGE_ON_HIT, $pageHit);
    }

    public function onPageHit(PageHitEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            PageEvents::PAGE_ON_HIT,
            [
                'hit' => $event->getHit(),
            ],
            [
                'hitDetails',
                'emailDetails',
                'pageList',
                'leadList',
            ]
        );
    }
}
