<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncProcess;

use Milex\IntegrationsBundle\Event\SyncEvent;
use Milex\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Milex\IntegrationsBundle\IntegrationEvents;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\ObjectIdsDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\Exception\HandlerNotSupportedException;
use Milex\IntegrationsBundle\Sync\Helper\MappingHelper;
use Milex\IntegrationsBundle\Sync\Helper\RelationsHelper;
use Milex\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use Milex\IntegrationsBundle\Sync\Logger\DebugLogger;
use Milex\IntegrationsBundle\Sync\Notification\Notifier;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MilexSyncProcess;
use Milex\IntegrationsBundle\Sync\SyncService\SyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SyncProcess
{
    /**
     * @var MappingManualDAO
     */
    private $mappingManualDAO;

    /**
     * @var MilexSyncDataExchange
     */
    private $internalSyncDataExchange;

    /**
     * @var SyncDataExchangeInterface
     */
    private $integrationSyncDataExchange;

    /**
     * @var SyncDateHelper
     */
    private $syncDateHelper;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var RelationsHelper
     */
    private $relationsHelper;

    /**
     * @var IntegrationSyncProcess
     */
    private $integrationSyncProcess;

    /**
     * @var MilexSyncProcess
     */
    private $milexSyncProcess;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var InputOptionsDAO
     */
    private $inputOptionsDAO;

    /**
     * @var int
     */
    private $syncIteration;

    /**
     * @var SyncService
     */
    private $syncService;

    public function __construct(
        SyncDateHelper $syncDateHelper,
        MappingHelper $mappingHelper,
        RelationsHelper $relationsHelper,
        IntegrationSyncProcess $integrationSyncProcess,
        MilexSyncProcess $milexSyncProcess,
        EventDispatcherInterface $eventDispatcher,
        Notifier $notifier,
        MappingManualDAO $mappingManualDAO,
        SyncDataExchangeInterface $internalSyncDataExchange,
        SyncDataExchangeInterface $integrationSyncDataExchange,
        InputOptionsDAO $inputOptionsDAO,
        SyncService $syncService
    ) {
        $this->syncDateHelper              = $syncDateHelper;
        $this->mappingHelper               = $mappingHelper;
        $this->relationsHelper             = $relationsHelper;
        $this->integrationSyncProcess      = $integrationSyncProcess;
        $this->milexSyncProcess           = $milexSyncProcess;
        $this->eventDispatcher             = $eventDispatcher;
        $this->notifier                    = $notifier;
        $this->mappingManualDAO            = $mappingManualDAO;
        $this->internalSyncDataExchange    = $internalSyncDataExchange;
        $this->integrationSyncDataExchange = $integrationSyncDataExchange;
        $this->inputOptionsDAO             = $inputOptionsDAO;
        $this->syncService                 = $syncService;
    }

    /**
     * Execute sync with integration.
     */
    public function execute(): void
    {
        defined('MILEX_INTEGRATION_ACTIVE_SYNC') or define('MILEX_INTEGRATION_ACTIVE_SYNC', 1);

        // Setup/prepare for the sync
        $this->syncDateHelper->setSyncDateTimes($this->inputOptionsDAO->getStartDateTime(), $this->inputOptionsDAO->getEndDateTime());
        $this->integrationSyncProcess->setupSync($this->inputOptionsDAO, $this->mappingManualDAO, $this->integrationSyncDataExchange);
        $this->milexSyncProcess->setupSync($this->inputOptionsDAO, $this->mappingManualDAO, $this->internalSyncDataExchange);

        if ($this->inputOptionsDAO->pullIsEnabled()) {
            $this->executeIntegrationSync();
        }

        if ($this->inputOptionsDAO->pushIsEnabled()) {
            $this->executeInternalSync();
        }

        // Tell listeners sync is done
        $this->eventDispatcher->dispatch(
            IntegrationEvents::INTEGRATION_POST_EXECUTE,
            new SyncEvent($this->mappingManualDAO->getIntegration(), $this->inputOptionsDAO->getStartDateTime(), $this->inputOptionsDAO->getEndDateTime())
        );
    }

    private function executeIntegrationSync(): void
    {
        $this->syncIteration = 1;
        do {
            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf('Integration to Milex; syncing iteration %s', $this->syncIteration),
                __CLASS__.':'.__FUNCTION__
            );

            $syncReport = $this->integrationSyncProcess->getSyncReport($this->syncIteration);
            if (!$syncReport->shouldSync()) {
                DebugLogger::log(
                    $this->mappingManualDAO->getIntegration(),
                    'Integration to Milex; no objects were mapped to be synced',
                    __CLASS__.':'.__FUNCTION__
                );

                break;
            }

            // Update the mappings in case objects have been converted such as Lead -> Contact
            $this->mappingHelper->remapIntegrationObjects($syncReport->getRemappedObjects());

            // Maps relations, synchronizes missing objects if necessary
            $this->manageRelations($syncReport);

            // Convert the integrations' report into an "order" or instructions for Milex
            $syncOrder = $this->milexSyncProcess->getSyncOrder($syncReport, $this->inputOptionsDAO->isFirstTimeSync(), $this->mappingManualDAO);
            if (!$syncOrder->shouldSync()) {
                DebugLogger::log(
                    $this->mappingManualDAO->getIntegration(),
                    'Integration to Milex; no object changes were recorded possible due to field direction configurations',
                    __CLASS__.':'.__FUNCTION__
                );

                break;
            }

            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf(
                    'Integration to Milex; syncing %d total objects',
                    $syncOrder->getObjectCount()
                ),
                __CLASS__.':'.__FUNCTION__
            );

            // Execute the sync instructions
            $this->internalSyncDataExchange->executeSyncOrder($syncOrder);

            if ($this->shouldStopIntegrationSync()) {
                break;
            }

            // Fetch the next iteration/batch
            ++$this->syncIteration;
        } while (true);
    }

    private function executeInternalSync(): void
    {
        $this->syncIteration = 1;
        do {
            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf('Milex to integration; syncing iteration %s', $this->syncIteration),
                __CLASS__.':'.__FUNCTION__
            );

            $syncReport = $this->milexSyncProcess->getSyncReport($this->syncIteration, $this->inputOptionsDAO);

            if (!$syncReport->shouldSync()) {
                DebugLogger::log(
                    $this->mappingManualDAO->getIntegration(),
                    'Milex to integration; no objects were mapped to be synced',
                    __CLASS__.':'.__FUNCTION__
                );

                break;
            }

            // Convert the internal report into an "order" or instructions for the integration
            $syncOrder = $this->integrationSyncProcess->getSyncOrder($syncReport, $this->inputOptionsDAO->isFirstTimeSync(), $this->mappingManualDAO);

            if (!$syncOrder->shouldSync()) {
                DebugLogger::log(
                    $this->mappingManualDAO->getIntegration(),
                    'Milex to integration; no object changes were recorded possible due to field direction configurations',
                    __CLASS__.':'.__FUNCTION__
                );

                // Finalize notifications such as injecting user notifications
                $this->notifier->finalizeNotifications();

                break;
            }

            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf(
                    'Milex to integration; syncing %d total objects',
                    $syncOrder->getObjectCount()
                ),
                __CLASS__.':'.__FUNCTION__
            );

            // Execute the sync instructions
            $this->integrationSyncDataExchange->executeSyncOrder($syncOrder);

            // Save mappings and cleanup
            $this->finalizeSync($syncOrder);

            // Fetch the next iteration/batch
            ++$this->syncIteration;
        } while (true);
    }

    private function manageRelations(ReportDAO $syncReport): void
    {
        // Map relations
        $this->relationsHelper->processRelations($this->mappingManualDAO, $syncReport);

        // Relation objects we need to synchronize
        $objectsToSynchronize = $this->relationsHelper->getObjectsToSynchronize();

        if (!empty($objectsToSynchronize)) {
            $this->synchronizeMissingObjects($objectsToSynchronize, $syncReport);
        }
    }

    private function synchronizeMissingObjects(array $objectsToSynchronize, ReportDAO $syncReport): void
    {
        $inputOptions = $this->getInputOptionsForObjects($objectsToSynchronize);

        // We need to synchronize missing relation ids
        $this->processParallelSync($inputOptions);

        // Now we can map relations for objects we have just synchronised
        $this->relationsHelper->processRelations($this->mappingManualDAO, $syncReport);
    }

    /**
     * @throws \Milex\IntegrationsBundle\Exception\InvalidValueException
     */
    private function getInputOptionsForObjects(array $objectsToSynchronize): InputOptionsDAO
    {
        $milexObjectIds = new ObjectIdsDAO();

        foreach ($objectsToSynchronize as $object) {
            $milexObjectIds->addObjectId($object->getObject(), $object->getObjectId());
        }

        $integration  = $this->mappingManualDAO->getIntegration();

        return new InputOptionsDAO([
            'integration'           => $integration,
            'integration-object-id' => $milexObjectIds,
        ]);
    }

    /**
     * @param $inputOptions
     *
     * @throws IntegrationNotFoundException
     */
    private function processParallelSync($inputOptions): void
    {
        $currentSyncProcess = clone $this->integrationSyncProcess;
        $this->syncService->processIntegrationSync($inputOptions);

        // We need to bring back current $inputOptions which were overwritten by new sync
        $this->integrationSyncProcess = $currentSyncProcess;
    }

    private function shouldStopIntegrationSync(): bool
    {
        // We don't want to iterate sync for specific ids
        return null !== $this->inputOptionsDAO->getIntegrationObjectIds();
    }

    /**
     * @throws IntegrationNotFoundException
     * @throws HandlerNotSupportedException
     */
    private function finalizeSync(OrderDAO $syncOrder): void
    {
        // Save the mappings between Milex objects and the integration's objects
        $this->mappingHelper->saveObjectMappings($syncOrder->getObjectMappings());

        // Remap integration objects to Milex objects if applicable
        $this->mappingHelper->remapIntegrationObjects($syncOrder->getRemappedObjects());

        // Update last sync dates on existing object mappings
        $this->mappingHelper->updateObjectMappings($syncOrder->getUpdatedObjectMappings());

        // Tell sync that these objects have been deleted and not to continue re-syncing them
        $this->mappingHelper->markAsDeleted($syncOrder->getDeletedObjects());

        // Inject notifications
        $this->notifier->noteMilexSyncIssue($syncOrder->getNotifications());

        // Cleanup field tracking for successfully synced objects
        $this->internalSyncDataExchange->cleanupProcessedObjects($syncOrder->getSuccessfullySyncedObjects());
    }
}
