<?php

namespace MilexPlugin\MilexCrmBundle\EventListener;

use Milex\LeadBundle\Event as Events;
use Milex\LeadBundle\LeadEvents;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Export\CompanyExport;
use MilexPlugin\MilexCrmBundle\Integration\PipedriveIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CompanySubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var CompanyExport
     */
    private $companyExport;

    public function __construct(IntegrationHelper $integrationHelper, CompanyExport $companyExport)
    {
        $this->integrationHelper = $integrationHelper;
        $this->companyExport     = $companyExport;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::COMPANY_POST_SAVE  => ['onCompanyPostSave', 0],
            LeadEvents::COMPANY_PRE_DELETE => ['onCompanyPreDelete', 10],
        ];
    }

    /**
     * @param Events\LeadEvent $event
     */
    public function onCompanyPostSave(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        if ($company->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }

        $this->companyExport->setIntegration($integrationObject);
        $this->companyExport->pushCompany($company);
    }

    /**
     * @param Events\LeadEvent $event
     */
    public function onCompanyPreDelete(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        if ($company->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }

        $this->companyExport->setIntegration($integrationObject);
        $this->companyExport->delete($company);
    }
}
