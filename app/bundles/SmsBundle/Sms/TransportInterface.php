<?php

namespace Milex\SmsBundle\Sms;

use Milex\LeadBundle\Entity\Lead;

interface TransportInterface
{
    /**
     * @param string $content
     *
     * @return bool
     */
    public function sendSms(Lead $lead, $content);
}
