<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Integration;

use Milex\IntegrationsBundle\Exception\InvalidValueException;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\FieldMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\Logger\DebugLogger;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper;

class ObjectChangeGenerator
{
    /**
     * @var ValueHelper
     */
    private $valueHelper;

    /**
     * @var ReportDAO
     */
    private $syncReport;

    /**
     * @var MappingManualDAO
     */
    private $mappingManual;

    /**
     * @var ReportObjectDAO
     */
    private $internalObject;

    /**
     * @var ReportObjectDAO
     */
    private $integrationObject;

    /**
     * @var ObjectChangeDAO
     */
    private $objectChange;

    public function __construct(ValueHelper $valueHelper)
    {
        $this->valueHelper = $valueHelper;
    }

    /**
     * @return ObjectChangeDAO
     *
     * @throws ObjectNotFoundException
     */
    public function getSyncObjectChange(
        ReportDAO $syncReport,
        MappingManualDAO $mappingManual,
        ObjectMappingDAO $objectMapping,
        ReportObjectDAO $internalObject,
        ReportObjectDAO $integrationObject
    ) {
        $this->syncReport        = $syncReport;
        $this->mappingManual     = $mappingManual;
        $this->internalObject    = $internalObject;
        $this->integrationObject = $integrationObject;

        $this->objectChange = new ObjectChangeDAO(
            $this->mappingManual->getIntegration(),
            $integrationObject->getObject(),
            $integrationObject->getObjectId(),
            $internalObject->getObject(),
            $internalObject->getObjectId()
        );

        if ($integrationObject->getObjectId()) {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    "Milex to integration; found a match between the integration %s:%s object and Milex's %s:%s object",
                    $integrationObject->getObject(),
                    (string) $integrationObject->getObjectId(),
                    $internalObject->getObject(),
                    (string) $internalObject->getObjectId()
                ),
                __CLASS__.':'.__FUNCTION__
            );
        } else {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    'Milex to integration: no match found for %s:%s',
                    $internalObject->getObject(),
                    (string) $internalObject->getObjectId()
                ),
                __CLASS__.':'.__FUNCTION__
            );
        }

        /** @var FieldMappingDAO[] $fieldMappings */
        $fieldMappings = $objectMapping->getFieldMappings();
        foreach ($fieldMappings as $fieldMappingDAO) {
            $this->addFieldToObjectChange($fieldMappingDAO);
        }

        // Set the change date/time from the object so that we can update last sync date based on this
        $this->objectChange->setChangeDateTime($internalObject->getChangeDateTime());

        return $this->objectChange;
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function addFieldToObjectChange(FieldMappingDAO $fieldMappingDAO): void
    {
        try {
            $fieldState = $this->internalObject->getField($fieldMappingDAO->getInternalField())->getState();

            $internalInformationChangeRequest = $this->syncReport->getInformationChangeRequest(
                $this->internalObject->getObject(),
                $this->internalObject->getObjectId(),
                $fieldMappingDAO->getInternalField()
            );
        } catch (FieldNotFoundException $e) {
            return;
        }

        try {
            $newValue = $this->valueHelper->getValueForIntegration(
                $internalInformationChangeRequest->getNewValue(),
                $fieldState,
                $fieldMappingDAO->getSyncDirection()
            );
        } catch (InvalidValueException $e) {
            return; // Field has to be skipped
        }

        // Note: bidirectional conflicts were handled by Internal\ObjectChangeGenerator
        $this->objectChange->addField(
            new FieldDAO($fieldMappingDAO->getIntegrationField(), $newValue),
            $fieldState
        );

        /*
         * Below here is just debug logging
         */

        // ObjectMappingDAO::SYNC_TO_MILEX
        if (ObjectMappingDAO::SYNC_TO_MILEX === $fieldMappingDAO->getSyncDirection()) {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    "Milex to integration; the %s object's %s field %s was added to the list of %s fields",
                    $this->integrationObject->getObject(),
                    $fieldState,
                    $fieldMappingDAO->getIntegrationField(),
                    $fieldState
                ),
                __CLASS__.':'.__FUNCTION__
            );

            return;
        }

        // ObjectMappingDAO::SYNC_TO_INTEGRATION
        // ObjectMappingDAO::SYNC_BIDIRECTIONALLY
        DebugLogger::log(
            $this->mappingManual->getIntegration(),
            sprintf(
                "Milex to integration; syncing %s object's %s field %s with a value of %s",
                $this->integrationObject->getObject(),
                $fieldState,
                $fieldMappingDAO->getIntegrationField(),
                var_export($newValue->getNormalizedValue(), true)
            ),
            __CLASS__.':'.__FUNCTION__
        );
    }
}
