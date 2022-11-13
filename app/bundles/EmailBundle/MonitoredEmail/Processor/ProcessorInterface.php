<?php

namespace Milex\EmailBundle\MonitoredEmail\Processor;

use Milex\EmailBundle\MonitoredEmail\Message;

interface ProcessorInterface
{
    /**
     * Process the message.
     *
     * @return bool
     */
    public function process(Message $message);
}
