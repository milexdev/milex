<?php

namespace Milex\EmailBundle\Swiftmailer\Transport;

use Milex\EmailBundle\MonitoredEmail\Exception\BounceNotFound;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;

/**
 * Interface InterfaceBounceProcessor.
 */
interface BounceProcessorInterface
{
    /**
     * Get the email address that bounced.
     *
     * @return BouncedEmail
     *
     * @throws BounceNotFound
     */
    public function processBounce(Message $message);
}
