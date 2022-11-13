<?php

namespace Milex\EmailBundle\EventListener;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event\TransportWebhookEvent;
use Milex\EmailBundle\Helper\RequestStorageHelper;
use Milex\EmailBundle\Swiftmailer\Momentum\Callback\MomentumCallbackInterface;
use Milex\EmailBundle\Swiftmailer\Transport\MomentumTransport;
use Milex\QueueBundle\Event\QueueConsumerEvent;
use Milex\QueueBundle\Queue\QueueConsumerResults;
use Milex\QueueBundle\Queue\QueueName;
use Milex\QueueBundle\Queue\QueueService;
use Milex\QueueBundle\QueueEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MomentumSubscriber implements EventSubscriberInterface
{
    /**
     * @var MomentumCallbackInterface
     */
    private $momentumCallback;

    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * @var RequestStorageHelper
     */
    private $requestStorageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MomentumCallbackInterface $momentumCallback,
        QueueService $queueService,
        RequestStorageHelper $requestStorageHelper,
        LoggerInterface $logger
    ) {
        $this->momentumCallback     = $momentumCallback;
        $this->queueService         = $queueService;
        $this->requestStorageHelper = $requestStorageHelper;
        $this->logger               = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            QueueEvents::TRANSPORT_WEBHOOK    => ['onMomentumWebhookQueueProcessing', 0],
            EmailEvents::ON_TRANSPORT_WEBHOOK => ['onMomentumWebhookRequest', 0],
        ];
    }

    /**
     * Webhook handling specific to Momentum transport.
     */
    public function onMomentumWebhookQueueProcessing(QueueConsumerEvent $event)
    {
        if ($event->checkTransport(MomentumTransport::class)) {
            $payload = $event->getPayload();
            $key     = $payload['key'];

            try {
                $request = $this->requestStorageHelper->getRequest($key);
                $this->momentumCallback->processCallbackRequest($request);
                $this->requestStorageHelper->deleteCachedRequest($key);
            } catch (\UnexpectedValueException $e) {
                $this->logger->error($e->getMessage());
            }

            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
        }
    }

    public function onMomentumWebhookRequest(TransportWebhookEvent $event)
    {
        $transport = MomentumTransport::class;
        if ($this->queueService->isQueueEnabled() && $event->transportIsInstanceOf($transport)) {
            // Beanstalk jobs are limited to 65,535 kB. Momentum can send up to 10.000 items per request.
            // One item has about 1,6 kB. Lets store the request to the cache storage instead of the job itself.
            $key = $this->requestStorageHelper->storeRequest($transport, $event->getRequest());
            $this->queueService->publishToQueue(QueueName::TRANSPORT_WEBHOOK, ['transport' => $transport, 'key' => $key]);
            $event->stopPropagation();
        }

        // If the queue processing is disabled do nothing and let the default listener to process immediately
    }
}
