<?php

namespace Milex\EmailBundle\Tests\EventListener;

use Milex\EmailBundle\Event\TransportWebhookEvent;
use Milex\EmailBundle\EventListener\MomentumSubscriber;
use Milex\EmailBundle\Helper\RequestStorageHelper;
use Milex\EmailBundle\Swiftmailer\Momentum\Callback\MomentumCallbackInterface;
use Milex\EmailBundle\Swiftmailer\Transport\MomentumTransport;
use Milex\QueueBundle\Event\QueueConsumerEvent;
use Milex\QueueBundle\Queue\QueueConsumerResults;
use Milex\QueueBundle\Queue\QueueName;
use Milex\QueueBundle\Queue\QueueService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class MomentumSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private $queueServiceMock;
    private $momentumCallbackMock;
    private $requestStorageHelperMock;
    private $loggerMock;
    private $momentumSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->momentumCallbackMock     = $this->createMock(MomentumCallbackInterface::class);
        $this->queueServiceMock         = $this->createMock(QueueService::class);
        $this->requestStorageHelperMock = $this->createMock(RequestStorageHelper::class);
        $this->loggerMock               = $this->createMock(LoggerInterface::class);
        $this->momentumSubscriber       = new MomentumSubscriber(
            $this->momentumCallbackMock,
            $this->queueServiceMock,
            $this->requestStorageHelperMock,
            $this->loggerMock
        );
    }

    public function testOnMomentumWebhookQueueProcessingForNonMomentumTransport()
    {
        $queueConsumerEvent = $this->createMock(QueueConsumerEvent::class);

        $queueConsumerEvent->expects($this->once())
        ->method('checkTransport')
        ->with(MomentumTransport::class)
        ->willReturn(false);

        $queueConsumerEvent->expects($this->never())
            ->method('getPayload');

        $this->momentumCallbackMock->expects($this->never())
            ->method('processCallbackRequest');

        $queueConsumerEvent->expects($this->never())
            ->method('setResult');

        $this->momentumSubscriber->onMomentumWebhookQueueProcessing($queueConsumerEvent);
    }

    public function testOnMomentumWebhookQueueProcessingForMomentumTransport()
    {
        $queueConsumerEvent = $this->createMock(QueueConsumerEvent::class);

        $queueConsumerEvent->expects($this->once())
            ->method('getPayload')
            ->willReturn([
                'transport' => MomentumTransport::class,
                'key'       => 'value',
            ]);

        $queueConsumerEvent->expects($this->once())
            ->method('checkTransport')
            ->with(MomentumTransport::class)
            ->willReturn(true);

        $this->requestStorageHelperMock->expects($this->once())
            ->method('getRequest')
            ->with('value')
            ->willReturn(new Request([], ['request' => 'value']));

        $this->momentumCallbackMock->expects($this->once())
            ->method('processCallbackRequest')
            ->with($this->callback(function ($request) {
                $requestValues = $request->request->all();
                $this->assertEquals(['request' => 'value'], $requestValues);

                return true;
            }));

        $queueConsumerEvent->expects($this->once())
            ->method('setResult')
            ->with(QueueConsumerResults::ACKNOWLEDGE);

        $this->momentumSubscriber->onMomentumWebhookQueueProcessing($queueConsumerEvent);
    }

    public function testOnMomentumWebhookQueueProcessingForMomentumTransportIfRequestNotFounc()
    {
        $queueConsumerEvent = $this->createMock(QueueConsumerEvent::class);

        $queueConsumerEvent->expects($this->once())
            ->method('getPayload')
            ->willReturn([
                'transport' => MomentumTransport::class,
                'key'       => 'value',
            ]);

        $queueConsumerEvent->expects($this->once())
            ->method('checkTransport')
            ->with(MomentumTransport::class)
            ->willReturn(true);

        $this->requestStorageHelperMock->expects($this->once())
            ->method('getRequest')
            ->with('value')
            ->will($this->throwException(new \UnexpectedValueException('Error message')));

        $this->momentumCallbackMock->expects($this->never())
            ->method('processCallbackRequest');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Error message');

        $queueConsumerEvent->expects($this->once())
            ->method('setResult')
            ->with(QueueConsumerResults::ACKNOWLEDGE);

        $this->momentumSubscriber->onMomentumWebhookQueueProcessing($queueConsumerEvent);
    }

    public function testOnMomentumWebhookRequestWhenQueueIsDisabled()
    {
        $transportWebhookEvent = $this->createMock(TransportWebhookEvent::class);

        $this->queueServiceMock->expects($this->once())
            ->method('isQueueEnabled')
            ->willReturn(false);

        $transportWebhookEvent->expects($this->never())
            ->method('getRequest');

        $this->momentumSubscriber->onMomentumWebhookRequest($transportWebhookEvent);
    }

    public function testOnMomentumWebhookRequestWhenQueueIsEnabled()
    {
        $transportWebhookEvent = $this->createMock(TransportWebhookEvent::class);
        $request               = new Request([], ['one', 'two', 'three']);
        $key                   = 'Milex\EmailBundle\Swiftmailer\Transport\MomentumTransport:webhook_request:5b43832134cfb0.36545510';

        $this->queueServiceMock->expects($this->once())
            ->method('isQueueEnabled')
            ->willReturn(true);

        $transportWebhookEvent->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $transportWebhookEvent->expects($this->once())
            ->method('transportIsInstanceOf')
            ->willReturn(true);

        $this->requestStorageHelperMock->expects($this->once())
            ->method('storeRequest')
            ->with(MomentumTransport::class, $request)
            ->willReturn($key);

        $this->queueServiceMock->expects($this->once())
            ->method('publishToQueue')
            ->with(QueueName::TRANSPORT_WEBHOOK, ['transport' => MomentumTransport::class, 'key' => $key]);

        $transportWebhookEvent->expects($this->once())
            ->method('stopPropagation');

        $this->momentumSubscriber->onMomentumWebhookRequest($transportWebhookEvent);
    }
}
