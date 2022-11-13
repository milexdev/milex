<?php

namespace Milex\PluginBundle\EventListener;

use Milex\LeadBundle\Event\CompanyEvent;
use Milex\LeadBundle\Event\LeadEvent;
use Milex\LeadBundle\LeadEvents;
use Milex\PluginBundle\Model\PluginModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var PluginModel
     */
    private $pluginModel;

    public function __construct(PluginModel $pluginModel)
    {
        $this->pluginModel = $pluginModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_PRE_DELETE    => ['onLeadDelete', 0],
            LeadEvents::LEAD_POST_SAVE     => ['onLeadSave', 0],
            LeadEvents::COMPANY_PRE_DELETE => ['onCompanyDelete', 0],
        ];
    }

    /*
     * Delete lead event
     */
    public function onLeadDelete(LeadEvent $event)
    {
        /** @var \Milex\LeadBundle\Entity\Lead $lead */
        $lead                  = $event->getLead();
        $integrationEntityRepo = $this->pluginModel->getIntegrationEntityRepository();
        $integrationEntityRepo->findLeadsToDelete('lead%', $lead->getId());

        return false;
    }

    /*
     * Delete company event
     */
    public function onCompanyDelete(CompanyEvent $event)
    {
        /** @var \Milex\LeadBundle\Entity\Company $company */
        $company               = $event->getCompany();
        $integrationEntityRepo = $this->pluginModel->getIntegrationEntityRepository();
        $integrationEntityRepo->findLeadsToDelete('company%', $company->getId());

        return false;
    }

    /*
    * Change lead event
    */
    public function onLeadSave(LeadEvent $event)
    {
        /** @var \Milex\LeadBundle\Entity\Lead $lead */
        $lead                  = $event->getLead();
        $integrationEntityRepo = $this->pluginModel->getIntegrationEntityRepository();
        $integrationEntityRepo->updateErrorLeads('lead-error', $lead->getId());
    }
}
