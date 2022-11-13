<?php

namespace Milex\SmsBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\SmsBundle\Entity\Sms;

/**
 * Class SmsEvent.
 */
class SmsEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Sms $sms, $isNew = false)
    {
        $this->entity = $sms;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Sms entity.
     *
     * @return Sms
     */
    public function getSms()
    {
        return $this->entity;
    }

    /**
     * Sets the Sms entity.
     */
    public function setSms(Sms $sms)
    {
        $this->entity = $sms;
    }
}
