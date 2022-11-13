<?php

namespace Milex\CampaignBundle\Event;

use Milex\CampaignBundle\Entity\Campaign;
use Milex\CoreBundle\Event\CommonEvent;

/**
 * Class CampaignEvent.
 */
class CampaignEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Campaign &$campaign, $isNew = false)
    {
        $this->entity = &$campaign;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Campaign entity.
     *
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->entity;
    }

    /**
     * Sets the Campaign entity.
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->entity = $campaign;
    }
}
