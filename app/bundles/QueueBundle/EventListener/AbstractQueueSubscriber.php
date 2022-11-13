<?php

namespace Milex\QueueBundle\EventListener;

use Milex\QueueBundle\Event as Events;
use Milex\QueueBundle\QueueEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractQueueSubscriber implements EventSubscriberInterface
{
    protected $protocol              = '';
    protected $protocolUiTranslation = '';

    abstract public function publishMessage(Events\QueueEvent $event);

    abstract public function consumeMessage(Events\QueueEvent $event);

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            QueueEvents::PUBLISH_MESSAGE => ['onPublishMessage', 0],
            QueueEvents::CONSUME_MESSAGE => ['onConsumeMessage', 0],
        ];
    }

    public function onPublishMessage(Events\QueueEvent $event)
    {
        if (!$event->checkContext($this->protocol)) {
            return;
        }

        $this->publishMessage($event);
    }

    public function onConsumeMessage(Events\QueueEvent $event)
    {
        if (!$event->checkContext($this->protocol)) {
            return;
        }

        $this->consumeMessage($event);
    }
}
