<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncDataExchange;

use Milex\IntegrationsBundle\Entity\FieldChangeRepository;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use Milex\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use Milex\IntegrationsBundle\Sync\Helper\MappingHelper;
use Milex\IntegrationsBundle\Sync\Logger\DebugLogger;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder;

class MilexSyncDataExchange implements SyncDataExchangeInterface
{
    const NAME           = 'milex';
    const OBJECT_CONTACT = 'lead'; // kept as lead for BC
    const OBJECT_COMPANY = 'company';

    /**
     * @var FieldChangeRepository
     */
    private $fieldChangeRepository;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var FullObjectReportBuilder
     */
    private $fullObjectReportBuilder;

    /**
     * @var PartialObjectReportBuilder
     */
    private $partialObjectReportBuilder;

    /**
     * @var OrderExecutioner
     */
    private $orderExecutioner;

    public function __construct(
        FieldChangeRepository $fieldChangeRepository,
        FieldHelper $fieldHelper,
        MappingHelper $mappingHelper,
        FullObjectReportBuilder $fullObjectReportBuilder,
        PartialObjectReportBuilder $partialObjectReportBuilder,
        OrderExecutioner $orderExecutioner
    ) {
        $this->fieldChangeRepository      = $fieldChangeRepository;
        $this->fieldHelper                = $fieldHelper;
        $this->mappingHelper              = $mappingHelper;
        $this->fullObjectReportBuilder    = $fullObjectReportBuilder;
        $this->partialObjectReportBuilder = $partialObjectReportBuilder;
        $this->orderExecutioner           = $orderExecutioner;
    }

    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        if ($requestDAO->isFirstTimeSync() || $requestDAO->getInputOptionsDAO()->getMilexObjectIds()) {
            return $this->fullObjectReportBuilder->buildReport($requestDAO);
        }

        return $this->partialObjectReportBuilder->buildReport($requestDAO);
    }

    public function executeSyncOrder(OrderDAO $syncOrderDAO): void
    {
        $this->orderExecutioner->execute($syncOrderDAO);
    }

    /**
     * @return ReportObjectDAO
     *
     * @throws ObjectNotFoundException
     * @throws ObjectNotSupportedException
     * @throws ObjectDeletedException
     */
    public function getConflictedInternalObject(MappingManualDAO $mappingManualDAO, string $internalObjectName, ReportObjectDAO $integrationObjectDAO)
    {
        // Check to see if we have a match
        $internalObjectDAO = $this->mappingHelper->findMilexObject($mappingManualDAO, $internalObjectName, $integrationObjectDAO);

        if (!$internalObjectDAO->getObjectId()) {
            return new ReportObjectDAO($internalObjectName, null);
        }

        $fieldChanges = $this->fieldChangeRepository->findChangesForObject(
            $mappingManualDAO->getIntegration(),
            $this->mappingHelper->getMilexEntityClassName($internalObjectName),
            $internalObjectDAO->getObjectId()
        );

        foreach ($fieldChanges as $fieldChange) {
            $internalObjectDAO->addField(
                $this->fieldHelper->getFieldChangeObject($fieldChange)
            );
        }

        return $internalObjectDAO;
    }

    /**
     * @param ObjectChangeDAO[] $objectChanges
     */
    public function cleanupProcessedObjects(array $objectChanges): void
    {
        foreach ($objectChanges as $changedObjectDAO) {
            try {
                $object   = $this->fieldHelper->getFieldObjectName($changedObjectDAO->getMappedObject());
                $objectId = $changedObjectDAO->getMappedObjectId();

                $this->fieldChangeRepository->deleteEntitiesForObject((int) $objectId, $object, $changedObjectDAO->getIntegration());
            } catch (ObjectNotSupportedException $exception) {
                DebugLogger::log(
                    self::NAME,
                    $exception->getMessage(),
                    __CLASS__.':'.__FUNCTION__
                );
            }
        }
    }
}
