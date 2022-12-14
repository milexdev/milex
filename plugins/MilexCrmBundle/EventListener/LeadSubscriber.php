<?php

namespace MilexPlugin\MilexCrmBundle\EventListener;

use Milex\LeadBundle\Event as Events;
use Milex\LeadBundle\LeadEvents;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Export\LeadExport;
use MilexPlugin\MilexCrmBundle\Integration\PipedriveIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var LeadExport
     */
    private $leadExport;

    public function __construct(IntegrationHelper $integrationHelper, LeadExport $leadExport = null)
    {
        $this->integrationHelper = $integrationHelper;
        $this->leadExport        = $leadExport;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE      => ['onLeadPostSave', 0],
            LeadEvents::LEAD_PRE_DELETE     => ['onLeadPostDelete', 255],
            LeadEvents::LEAD_COMPANY_CHANGE => ['onLeadCompanyChange', 0],
        ];
    }

    public function onLeadPostSave(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->isAnonymous()) {
            // Ignore this contact
            return;
        }
        if ($lead->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }
        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }
        $this->leadExport->setIntegration($integrationObject);

        $changes = $lead->getChanges(true);
        if (!empty($changes['dateIdentified'])) {
            $this->leadExport->create($lead);
        } else {
            $this->leadExport->update($lead);
        }
    }

    public function onLeadPostDelete(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }
        $this->leadExport->setIntegration($integrationObject);
        $this->leadExport->delete($lead);
    }

    public function onLeadCompanyChange(Events\LeadChangeCompanyEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }
        $this->leadExport->setIntegration($integrationObject);
        $this->leadExport->update($lead);
    }
}
