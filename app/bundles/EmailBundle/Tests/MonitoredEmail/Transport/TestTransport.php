<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Transport;

use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;
use Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail;
use Milex\EmailBundle\Swiftmailer\Transport\BounceProcessorInterface;
use Milex\EmailBundle\Swiftmailer\Transport\UnsubscriptionProcessorInterface;

class TestTransport extends \Swift_Transport_NullTransport implements BounceProcessorInterface, UnsubscriptionProcessorInterface
{
    public function processBounce(Message $message)
    {
        return new BouncedEmail();
    }

    public function processUnsubscription(Message $message)
    {
        return new UnsubscribedEmail('contact@email.com', 'test+unsubscribe_123abc@test.com');
    }
}
