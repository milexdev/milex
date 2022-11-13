<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Internal;

use Milex\IntegrationsBundle\Exception\InvalidValueException;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\FieldMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO as ReportFieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use Milex\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\Logger\DebugLogger;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\IntegrationsBundle\Sync\SyncJudge\SyncJudgeInterface;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper;

class ObjectChangeGenerator
{
    /**
     * @var SyncJudgeInterface
     */
    private $syncJudge;

    /**
     * @var ReportDAO
     */
    private $syncReport;

    /**
     * @var ValueHelper
     */
    private $valueHelper;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

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

    /**
     * @var array
     */
    private $judgementModes = [
        SyncJudgeInterface::HARD_EVIDENCE_MODE,
        SyncJudgeInterface::BEST_EVIDENCE_MODE,
        SyncJudgeInterface::FUZZY_EVIDENCE_MODE,
    ];

    /**
     * ObjectChangeGenerator constructor.
     */
    public function __construct(SyncJudgeInterface $syncJudge, ValueHelper $valueHelper, FieldHelper $fieldHelper)
    {
        $this->syncJudge   = $syncJudge;
        $this->valueHelper = $valueHelper;
        $this->fieldHelper = $fieldHelper;
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
            $internalObject->getObject(),
            $internalObject->getObjectId(),
            $integrationObject->getObject(),
            $integrationObject->getObjectId()
        );

        if ($internalObject->getObjectId()) {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    "Integration to Milex; found a match between Milex's %s:%s object and the integration %s:%s object ",
                    $internalObject->getObject(),
                    (string) $internalObject->getObjectId(),
                    $integrationObject->getObject(),
                    (string) $integrationObject->getObjectId()
                ),
                __CLASS__.':'.__FUNCTION__
            );
        } else {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    'Integration to Milex; no match found for %s:%s',
                    $integrationObject->getObject(),
                    (string) $integrationObject->getObjectId()
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
        $this->objectChange->setChangeDateTime($integrationObject->getChangeDateTime());

        return $this->objectChange;
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function addFieldToObjectChange(FieldMappingDAO $fieldMappingDAO): void
    {
        try {
            $integrationFieldState = $this->integrationObject->getField($fieldMappingDAO->getIntegrationField())->getState();
            $internalFieldState    = $this->getFieldState(
                $fieldMappingDAO->getInternalObject(),
                $fieldMappingDAO->getInternalField(),
                $integrationFieldState
            );

            $integrationInformationChangeRequest = $this->syncReport->getInformationChangeRequest(
                $this->integrationObject->getObject(),
                $this->integrationObject->getObjectId(),
                $fieldMappingDAO->getIntegrationField()
            );
        } catch (FieldNotFoundException $e) {
            return;
        }

        // If syncing bidirectional, let the sync judge determine what value should be used for the field
        if (ObjectMappingDAO::SYNC_BIDIRECTIONALLY === $fieldMappingDAO->getSyncDirection()) {
            $this->judgeThenAddFieldToObjectChange($fieldMappingDAO, $integrationInformationChangeRequest, $internalFieldState);

            return;
        }

        try {
            $newValue = $this->valueHelper->getValueForMilex(
                $integrationInformationChangeRequest->getNewValue(),
                $internalFieldState,
                $fieldMappingDAO->getSyncDirection()
            );
        } catch (InvalidValueException $e) {
            return; // Field has to be skipped
        }

        // Add the value to the field based on the field state
        $this->objectChange->addField(
            new FieldDAO($fieldMappingDAO->getInternalField(), $newValue),
            $internalFieldState
        );

        /*
         * Below here is just debug logging
         */

        // ObjectMappingDAO::SYNC_TO_MILEX
        if (ObjectMappingDAO::SYNC_TO_MILEX === $fieldMappingDAO->getSyncDirection()) {
            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    'Integration to Milex; syncing %s %s with a value of %s',
                    $internalFieldState,
                    $fieldMappingDAO->getInternalField(),
                    var_export($newValue->getNormalizedValue(), true)
                ),
                __CLASS__.':'.__FUNCTION__
            );

            return;
        }

        // ObjectMappingDAO::SYNC_TO_INTEGRATION:
        DebugLogger::log(
            $this->mappingManual->getIntegration(),
            sprintf(
                "Integration to Milex; the %s object's %s field %s was added to the list of required fields because it's configured to sync to the integration",
                $this->internalObject->getObject(),
                $internalFieldState,
                $fieldMappingDAO->getInternalField()
            ),
            __CLASS__.':'.__FUNCTION__
        );
    }

    private function judgeThenAddFieldToObjectChange(
        FieldMappingDAO $fieldMappingDAO,
        InformationChangeRequestDAO $integrationInformationChangeRequest,
        string $fieldState
    ): void {
        try {
            $internalField = $this->internalObject->getField($fieldMappingDAO->getInternalField());
        } catch (FieldNotFoundException $exception) {
            $internalField = null;
        }

        if (!$internalField) {
            $newValue = $this->valueHelper->getValueForMilex(
                $integrationInformationChangeRequest->getNewValue(),
                $fieldState,
                $fieldMappingDAO->getSyncDirection()
            );

            $this->objectChange->addField(
                new FieldDAO($fieldMappingDAO->getInternalField(), $newValue),
                $fieldState
            );

            DebugLogger::log(
                $this->mappingManual->getIntegration(),
                sprintf(
                    "Integration to Milex; the sync is bidirectional but no conflicts were found so syncing the %s object's %s field %s with a value of %s",
                    $this->internalObject->getObject(),
                    $fieldState,
                    $fieldMappingDAO->getInternalField(),
                    var_export($newValue->getNormalizedValue(), true)
                ),
                __CLASS__.':'.__FUNCTION__
            );

            return;
        }

        $internalInformationChangeRequest = new InformationChangeRequestDAO(
            MilexSyncDataExchange::NAME,
            $this->internalObject->getObject(),
            $this->internalObject->getObjectId(),
            $internalField->getName(),
            $internalField->getValue()
        );

        $possibleChangeDateTime = $this->internalObject->getChangeDateTime();
        $certainChangeDateTime  = $internalField->getChangeDateTime();

        // If we know certain change datetime and it's newer than possible change datetime
        // then we have to update possible change datetime otherwise comparision doesn't work correctly
        if ($certainChangeDateTime && ($certainChangeDateTime > $possibleChangeDateTime)) {
            $possibleChangeDateTime = $certainChangeDateTime;
        }

        $internalInformationChangeRequest->setPossibleChangeDateTime($possibleChangeDateTime);
        $internalInformationChangeRequest->setCertainChangeDateTime($certainChangeDateTime);

        // There is a conflict so let the judge determine which value comes out on top
        foreach ($this->judgementModes as $judgeMode) {
            try {
                $this->makeJudgement(
                    $judgeMode,
                    $fieldMappingDAO,
                    $integrationInformationChangeRequest,
                    $internalInformationChangeRequest,
                    $fieldState
                );

                break;
            } catch (ConflictUnresolvedException $exception) {
                DebugLogger::log(
                    $this->mappingManual->getIntegration(),
                    sprintf(
                        'Integration to Milex; no winner was determined using the %s judging mode for object %s field %s',
                        $judgeMode,
                        $this->internalObject->getObject(),
                        $fieldMappingDAO->getInternalField()
                    ),
                    __CLASS__.':'.__FUNCTION__
                );
            }
        }
    }

    /**
     * @throws ConflictUnresolvedException
     */
    private function makeJudgement(
        string $judgeMode,
        FieldMappingDAO $fieldMappingDAO,
        InformationChangeRequestDAO $integrationInformationChangeRequest,
        InformationChangeRequestDAO $internalInformationChangeRequest,
        string $fieldState
    ): void {
        $winningChangeRequest = $this->syncJudge->adjudicate(
            $judgeMode,
            $internalInformationChangeRequest,
            $integrationInformationChangeRequest
        );

        $newValue = $this->valueHelper->getValueForMilex(
            $winningChangeRequest->getNewValue(),
            $fieldState,
            $fieldMappingDAO->getSyncDirection()
        );

        $this->objectChange->addField(
            new FieldDAO($fieldMappingDAO->getInternalField(), $newValue),
            $fieldState
        );

        DebugLogger::log(
            $this->mappingManual->getIntegration(),
            sprintf(
                "Integration to Milex; sync judge determined to sync %s to the %s object's %s field %s with a value of %s using the %s judging mode",
                $winningChangeRequest->getIntegration(),
                $winningChangeRequest->getObject(),
                $fieldState,
                $fieldMappingDAO->getInternalField(),
                var_export($newValue->getNormalizedValue(), true),
                $judgeMode
            ),
            __CLASS__.':'.__FUNCTION__
        );
    }

    /**
     * @return string
     */
    private function getFieldState(string $object, string $field, string $integrationFieldState)
    {
        // If this is a Milex required field, return required
        if (isset($this->fieldHelper->getRequiredFields($object)[$field])) {
            return ReportFieldDAO::FIELD_REQUIRED;
        }

        return $integrationFieldState;
    }
}
