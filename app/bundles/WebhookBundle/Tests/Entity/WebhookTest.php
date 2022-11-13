<?php

namespace Milex\WebhookBundle\Tests\Entity;

use Milex\WebhookBundle\Entity\Webhook;
use PHPUnit\Framework\Assert;

class WebhookTest extends \PHPUnit\Framework\TestCase
{
    public function testWasModifiedRecentlyWithNotModifiedWebhook()
    {
        $webhook = new Webhook();
        $this->assertNull($webhook->getDateModified());
        $this->assertFalse($webhook->wasModifiedRecently());
    }

    public function testWasModifiedRecentlyWithWebhookModifiedAWhileBack()
    {
        $webhook = new Webhook();
        $webhook->setDateModified((new \DateTime())->modify('-20 days'));
        $this->assertFalse($webhook->wasModifiedRecently());
    }

    public function testWasModifiedRecentlyWithWebhookModifiedRecently()
    {
        $webhook = new Webhook();
        $webhook->setDateModified((new \DateTime())->modify('-2 hours'));
        $this->assertTrue($webhook->wasModifiedRecently());
    }

    public function testTriggersFromApiAreStoredAsEvents(): void
    {
        $webhook  = new Webhook();
        $triggers = [
            'milex.company_post_save',
            'milex.company_post_delete',
            'milex.lead_channel_subscription_changed',
        ];

        $webhook->setTriggers($triggers);

        $events = $webhook->getEvents();
        Assert::assertCount(3, $events);

        foreach ($events as $key => $event) {
            Assert::assertEquals($event->getEventType(), $triggers[$key]);
            Assert::assertSame($webhook, $event->getWebhook());
        }
    }
}
