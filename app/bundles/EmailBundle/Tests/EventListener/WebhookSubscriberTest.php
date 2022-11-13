<?php

declare(strict_types=1);

namespace Milex\EmailBundle\Tests\EventListener;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Event\EmailSendEvent;
use Milex\EmailBundle\EventListener\WebhookSubscriber;
use Milex\LeadBundle\Entity\Lead;
use Milex\WebhookBundle\Event\WebhookBuilderEvent;
use Milex\WebhookBundle\Model\WebhookModel;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|WebhookModel
     */
    private $webhookModel;

    /**
     * @var WebhookSubscriber
     */
    private $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookModel = $this->createMock(WebhookModel::class);
        $this->subscriber   = new WebhookSubscriber($this->webhookModel);
    }

    public function testOnWebhookBuild(): void
    {
        $event = $this->createMock(WebhookBuilderEvent::class);

        $event->expects($this->exactly(2))
            ->method('addEvent')
            ->withConsecutive(
                [
                    EmailEvents::EMAIL_ON_SEND,
                    [
                        'label'       => 'milex.email.webhook.event.send',
                        'description' => 'milex.email.webhook.event.send_desc',
                    ],
                ],
                [
                    EmailEvents::EMAIL_ON_OPEN,
                    [
                        'label'       => 'milex.email.webhook.event.open',
                        'description' => 'milex.email.webhook.event.open_desc',
                    ],
                ]
            );

        $this->subscriber->onWebhookBuild($event);
    }

    public function testOnEmailSendWhenInternalSend(): void
    {
        $event = $this->createMock(EmailSendEvent::class);

        $event->expects($this->once())
            ->method('isInternalSend')
            ->willReturn(true);

        $this->webhookModel->expects($this->never())
            ->method('queueWebhooksByType');

        $this->subscriber->onEmailSend($event);
    }

    public function testOnEmailSend(): void
    {
        $event   = $this->createMock(EmailSendEvent::class);
        $contact = $this->createMock(Lead::class);
        $email   = $this->createMock(Email::class);
        $tokens  = ['{unsubscribe_text}' => '<a href=\"https://...'];
        $headers = ['List-Unsubscribe' => '<a href=\"https://...'];
        $source  = ['List-Unsubscribe' => '<a href=\"https://...']; // todo find out real source example

        $event->expects($this->once())
            ->method('isInternalSend')
            ->willReturn(false);

        $event->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $event->expects($this->any())
            ->method('getLead')
            ->willReturn($contact);

        $event->expects($this->once())
            ->method('getTokens')
            ->willReturn($tokens);

        $event->expects($this->once())
            ->method('getContentHash')
            ->willReturn('8bb9181fa1c76671352b79565e244240');

        $event->expects($this->once())
            ->method('getIdHash')
            ->willReturn('5cdaa50e155f9732391159');

        $event->expects($this->once())
            ->method('getContent')
            ->willReturn('<!DOCTYPE html><html>...');

        $event->expects($this->once())
            ->method('getSubject')
            ->willReturn('Test Email');

        $event->expects($this->once())
            ->method('getSource')
            ->willReturn($source);

        $event->expects($this->once())
            ->method('getTextHeaders')
            ->willReturn($headers);

        $this->webhookModel->expects($this->once())
            ->method('queueWebhooksByType')
            ->with(
                EmailEvents::EMAIL_ON_SEND,
                [
                    'email'       => $email,
                    'contact'     => $contact,
                    'tokens'      => $tokens,
                    'contentHash' => '8bb9181fa1c76671352b79565e244240',
                    'idHash'      => '5cdaa50e155f9732391159',
                    'content'     => '<!DOCTYPE html><html>...',
                    'subject'     => 'Test Email',
                    'source'      => $source,
                    'headers'     => $headers,
                ]
            );

        $this->subscriber->onEmailSend($event);
    }
}
