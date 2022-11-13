<?php

namespace Milex\CampaignBundle\Executioner\Scheduler\Mode;

use Milex\CampaignBundle\Entity\Event;

interface ScheduleModeInterface
{
    /**
     * @return \DateTime
     */
    public function getExecutionDateTime(Event $event, \DateTime $now, \DateTime $comparedToDateTime);
}
