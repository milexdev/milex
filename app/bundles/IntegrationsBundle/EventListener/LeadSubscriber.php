<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\EventListener;

use Milex\IntegrationsBundle\Entity\FieldChange;
use Milex\IntegrationsBundle\Entity\FieldChangeRepository;
use Milex\IntegrationsBundle\Entity\ObjectMappingRepository;
use Milex\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Milex\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\IntegrationsBundle\Sync\VariableExpresser\VariableExpresserHelperInterface;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Event as Events;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var FieldChangeRepository
     */
    private $fieldChangeRepo;

    /**
     * @var ObjectMappingRepository
     */
    private $objectMappingRepository;

    /**
     * @var VariableExpresserHelperInterface
     */
    private $variableExpressor;

    /**
     * @var SyncIntegrationsHelper
     */
    private $syncIntegrationsHelper;

    public function __construct(
        FieldChangeRepository $fieldChangeRepo,
        ObjectMappingRepository $objectMappingRepository,
        VariableExpresserHelperInterface $variableExpressor,
        SyncIntegrationsHelper $syncIntegrationsHelper
    ) {
        $this->fieldChangeRepo         = $fieldChangeRepo;
        $this->objectMappingRepository = $objectMappingRepository;
        $this->variableExpressor       = $variableExpressor;
        $this->syncIntegrationsHelper  = $syncIntegrationsHelper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::LEAD_POST_SAVE      => ['onLeadPostSave', 0],
            LeadEvents::LEAD_POST_DELETE    => ['onLeadPostDelete', 255],
            LeadEvents::COMPANY_POST_SAVE   => ['onCompanyPostSave', 0],
            LeadEvents::COMPANY_POST_DELETE => ['onCompanyPostDelete', 255],
            LeadEvents::LEAD_COMPANY_CHANGE => ['onLeadCompanyChange', 128],
        ];
    }

    /**
     * @throws IntegrationNotFoundException
     * @throws ObjectNotFoundException
     */
    public function onLeadPostSave(Events\LeadEvent $event): void
    {
        $lead = $event->getLead();
        if ($lead->isAnonymous()) {
            // Do not track visitor changes
            return;
        }

        if (defined('MILEX_INTEGRATION_SYNC_IN_PROGRESS')) {
            // Don't track changes just made by an active sync
            return;
        }

        if (!$this->syncIntegrationsHelper->hasObjectSyncEnabled(Contact::NAME)) {
            // Only track if an integration is syncing with contacts
            return;
        }

        $changes = $lead->getChanges(true);

        if (!empty($changes['owner'])) {
            // Force record of owner change if present in changelist
            $changes['fields']['owner_id'] = $changes['owner'];
        }

        if (!empty($changes['points'])) {
            // Add ability to update points custom field in target
            $changes['fields']['points'] = $changes['points'];
        }

        if (isset($changes['fields'])) {
            $this->recordFieldChanges($changes['fields'], $lead->getId(), Lead::class);
        }

        if (isset($changes['dnc_channel_status'])) {
            $dncChanges = [];
            foreach ($changes['dnc_channel_status'] as $channel => $change) {
                $oldValue = $change['old_reason'] ?? '';
                $newValue = $change['reason'];

                $dncChanges['milex_internal_dnc_'.$channel] = [$oldValue, $newValue];
            }

            $this->recordFieldChanges($dncChanges, $lead->getId(), Lead::class);
        }
    }

    public function onLeadPostDelete(Events\LeadEvent $event): void
    {
        if ($event->getLead()->isAnonymous()) {
            return;
        }

        $this->fieldChangeRepo->deleteEntitiesForObject((int) $event->getLead()->deletedId, Lead::class);
        $this->objectMappingRepository->deleteEntitiesForObject((int) $event->getLead()->deletedId, MilexSyncDataExchange::OBJECT_CONTACT);
    }

    /**
     * @throws IntegrationNotFoundException
     * @throws ObjectNotFoundException
     */
    public function onCompanyPostSave(Events\CompanyEvent $event): void
    {
        if (defined('MILEX_INTEGRATION_SYNC_IN_PROGRESS')) {
            // Don't track changes just made by an active sync
            return;
        }

        if (!$this->syncIntegrationsHelper->hasObjectSyncEnabled(MilexSyncDataExchange::OBJECT_COMPANY)) {
            // Only track if an integration is syncing with companies
            return;
        }

        $company = $event->getCompany();
        $changes = $company->getChanges(true);

        if (!empty($changes['owner'])) {
            // Force record of owner change if present in changelist
            $changes['fields']['owner_id'] = $changes['owner'];
        }

        if (!isset($changes['fields'])) {
            return;
        }

        $this->recordFieldChanges($changes['fields'], $company->getId(), Company::class);
    }

    public function onCompanyPostDelete(Events\CompanyEvent $event): void
    {
        $this->fieldChangeRepo->deleteEntitiesForObject((int) $event->getCompany()->deletedId, MilexSyncDataExchange::OBJECT_COMPANY);
        $this->objectMappingRepository->deleteEntitiesForObject((int) $event->getCompany()->deletedId, MilexSyncDataExchange::OBJECT_COMPANY);
    }

    public function onLeadCompanyChange(Events\LeadChangeCompanyEvent $event): void
    {
        $lead = $event->getLead();

        // This mechanism is not able to record multiple company changes.
        $changes['company'] = [
            0 => '',
            1 => $lead->getCompany(),
        ];

        $this->recordFieldChanges($changes, $lead->getId(), Lead::class);
    }

    /**
     * @param int $objectId
     *
     * @throws IntegrationNotFoundException
     */
    private function recordFieldChanges(array $fieldChanges, $objectId, string $objectType): void
    {
        $toPersist     = [];
        $changedFields = [];
        $objectId      = (int) $objectId;
        foreach ($fieldChanges as $key => [$oldValue, $newValue]) {
            $valueDAO          = $this->variableExpressor->encodeVariable($newValue);
            $changedFields[]   = $key;
            $fieldChangeEntity = (new FieldChange())
                ->setObjectType($objectType)
                ->setObjectId($objectId)
                ->setModifiedAt(new \DateTime())
                ->setColumnName($key)
                ->setColumnType($valueDAO->getType())
                ->setColumnValue($valueDAO->getValue());

            foreach ($this->syncIntegrationsHelper->getEnabledIntegrations() as $integrationName) {
                $integrationFieldChangeEntity = clone $fieldChangeEntity;
                $integrationFieldChangeEntity->setIntegration($integrationName);

                $toPersist[] = $integrationFieldChangeEntity;
            }
        }

        $this->fieldChangeRepo->deleteEntitiesForObjectByColumnName($objectId, $objectType, $changedFields);
        $this->fieldChangeRepo->saveEntities($toPersist);

        $this->fieldChangeRepo->clear();
    }
}
