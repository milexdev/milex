<?php

namespace MilexPlugin\MilexClearbitBundle\EventListener;

use Milex\LeadBundle\Event\CompanyEvent;
use Milex\LeadBundle\Event\LeadEvent;
use Milex\LeadBundle\LeadEvents;
use MilexPlugin\MilexClearbitBundle\Helper\LookupHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var LookupHelper
     */
    private $lookupHelper;

    public function __construct(LookupHelper $lookupHelper)
    {
        $this->lookupHelper = $lookupHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE    => ['leadPostSave', 0],
            LeadEvents::COMPANY_POST_SAVE => ['companyPostSave', 0],
        ];
    }

    public function leadPostSave(LeadEvent $event)
    {
        $this->lookupHelper->lookupContact($event->getLead(), true, true);
    }

    public function companyPostSave(CompanyEvent $event)
    {
        $this->lookupHelper->lookupCompany($event->getCompany(), true, true);
    }
}
