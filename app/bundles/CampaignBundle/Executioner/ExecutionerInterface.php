<?php

namespace Milex\CampaignBundle\Executioner;

use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use Symfony\Component\Console\Output\OutputInterface;

interface ExecutionerInterface
{
    /**
     * @return mixed
     */
    public function execute(Campaign $campaign, ContactLimiter $limiter, OutputInterface $output = null);
}
