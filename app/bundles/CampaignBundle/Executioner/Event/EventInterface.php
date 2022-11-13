<?php

namespace Milex\CampaignBundle\Executioner\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use Milex\CampaignBundle\Executioner\Result\EvaluatedContacts;

interface EventInterface
{
    /**
     * @return EvaluatedContacts
     */
    public function execute(AbstractEventAccessor $config, ArrayCollection $logs);
}
