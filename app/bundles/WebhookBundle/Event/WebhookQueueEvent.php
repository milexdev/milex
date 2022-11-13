<?php

namespace Milex\WebhookBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\WebhookBundle\Entity\Webhook;
use Milex\WebhookBundle\Entity\WebhookQueue;

/**
 * Class WebhookQueueEvent.
 */
class WebhookQueueEvent extends CommonEvent
{
    /**
     * @var Webhook
     */
    protected $webhook;

    /**
     * @param bool $isNew
     */
    public function __construct(WebhookQueue $webhookQueue, Webhook $webhook, $isNew = false)
    {
        $this->entity  = $webhookQueue;
        $this->webhook = $webhook;
        $this->isNew   = $isNew;
    }

    /**
     * Returns the WebhookQueue entity.
     *
     * @return WebhookQueue
     */
    public function getWebhookQueue()
    {
        return $this->entity;
    }

    /**
     * Sets the WebhookQueue entity.
     */
    public function setWebhookQueue(WebhookQueue $webhookQueue)
    {
        $this->entity = $webhookQueue;
    }

    /**
     * Returns the Webhook entity.
     *
     * @return Webhook
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * Sets the Webhook entity.
     */
    public function setWebhook(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }
}
