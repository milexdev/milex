<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncService;

use GuzzleHttp\Exception\ClientException;
use Milex\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use Milex\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Milex\IntegrationsBundle\Sync\Helper\MappingHelper;
use Milex\IntegrationsBundle\Sync\Helper\RelationsHelper;
use Milex\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use Milex\IntegrationsBundle\Sync\Logger\DebugLogger;
use Milex\IntegrationsBundle\Sync\Notification\Notifier;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MilexSyncProcess;
use Milex\IntegrationsBundle\Sync\SyncProcess\SyncProcess;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SyncService implements SyncServiceInterface
{
    /**
     * @var SyncDataExchangeInterface
     */
    private $internalSyncDataExchange;

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
    private $integratinSyncProcess;

    /**
     * @var MilexSyncProcess
     */
    private $milexSyncProcess;

    /**
     * @var SyncIntegrationsHelper
     */
    private $syncIntegrationsHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(
        MilexSyncDataExchange $internalSyncDataExchange,
        SyncDateHelper $syncDateHelper,
        MappingHelper $mappingHelper,
        RelationsHelper $relationsHelper,
        SyncIntegrationsHelper $syncIntegrationsHelper,
        EventDispatcherInterface $eventDispatcher,
        Notifier $notifier,
        IntegrationSyncProcess $integrationSyncProcess,
        MilexSyncProcess $milexSyncProcess
    ) {
        $this->internalSyncDataExchange = $internalSyncDataExchange;
        $this->syncDateHelper           = $syncDateHelper;
        $this->mappingHelper            = $mappingHelper;
        $this->relationsHelper          = $relationsHelper;
        $this->syncIntegrationsHelper   = $syncIntegrationsHelper;
        $this->eventDispatcher          = $eventDispatcher;
        $this->notifier                 = $notifier;
        $this->integratinSyncProcess    = $integrationSyncProcess;
        $this->milexSyncProcess        = $milexSyncProcess;
    }

    /**
     * @throws \Milex\IntegrationsBundle\Exception\IntegrationNotFoundException
     */
    public function processIntegrationSync(InputOptionsDAO $inputOptionsDAO): void
    {
        $integrationSyncProcess = new SyncProcess(
            $this->syncDateHelper,
            $this->mappingHelper,
            $this->relationsHelper,
            $this->integratinSyncProcess,
            $this->milexSyncProcess,
            $this->eventDispatcher,
            $this->notifier,
            $this->syncIntegrationsHelper->getMappingManual($inputOptionsDAO->getIntegration()),
            $this->internalSyncDataExchange,
            $this->syncIntegrationsHelper->getSyncDataExchange($inputOptionsDAO->getIntegration()),
            $inputOptionsDAO,
            $this
        );

        DebugLogger::log(
            $inputOptionsDAO->getIntegration(),
            sprintf(
                'Starting %s sync from %s date/time',
                $inputOptionsDAO->isFirstTimeSync() ? 'first time' : 'subsequent',
                $inputOptionsDAO->getStartDateTime() ? $inputOptionsDAO->getStartDateTime()->format('Y-m-d H:i:s') : 'yet to be determined'
            ),
            __CLASS__.':'.__FUNCTION__
        );

        try {
            $integrationSyncProcess->execute();
        } catch (ClientException $exception) {
            // The sync failed to communicate with the integration so log it
            DebugLogger::log($inputOptionsDAO->getIntegration(), $exception->getMessage(), null, [], LogLevel::ERROR);
        }
    }

    public function initiateDebugLogger(DebugLogger $logger): void
    {
        // Yes it's a hack to prevent from having to pass the logger as a dependency into dozens of classes
        // So not doing anything with the logger, just need Symfony to initiate the service
    }
}
