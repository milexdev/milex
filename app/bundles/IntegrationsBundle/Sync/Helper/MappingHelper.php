<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\Helper;

use Milex\IntegrationsBundle\Entity\ObjectMapping;
use Milex\IntegrationsBundle\Entity\ObjectMappingRepository;
use Milex\IntegrationsBundle\Event\InternalObjectFindEvent;
use Milex\IntegrationsBundle\IntegrationEvents;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\RemappedObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use Milex\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\LeadBundle\Model\FieldModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MappingHelper
{
    /**
     * @var FieldModel
     */
    private $fieldModel;

    /**
     * @var ObjectMappingRepository
     */
    private $objectMappingRepository;

    /**
     * @var ObjectProvider
     */
    private $objectProvider;

    /**
     * @var ObjectMappingRepository
     */
    private $dispatcher;

    public function __construct(
        FieldModel $fieldModel,
        ObjectMappingRepository $objectMappingRepository,
        ObjectProvider $objectProvider,
        EventDispatcherInterface $dispatcher
    ) {
        $this->fieldModel              = $fieldModel;
        $this->objectMappingRepository = $objectMappingRepository;
        $this->objectProvider          = $objectProvider;
        $this->dispatcher              = $dispatcher;
    }

    /**
     * @return ObjectDAO
     *
     * @throws ObjectDeletedException
     * @throws ObjectNotFoundException
     * @throws ObjectNotSupportedException
     */
    public function findMilexObject(MappingManualDAO $mappingManualDAO, string $internalObjectName, ObjectDAO $integrationObjectDAO)
    {
        // Check if this contact is already tracked
        if ($internalObject = $this->objectMappingRepository->getInternalObject(
            $mappingManualDAO->getIntegration(),
            $integrationObjectDAO->getObject(),
            $integrationObjectDAO->getObjectId(),
            $internalObjectName
        )) {
            if ($internalObject['is_deleted']) {
                throw new ObjectDeletedException();
            }

            return new ObjectDAO(
                $internalObjectName,
                $internalObject['internal_object_id'],
                new \DateTime($internalObject['last_sync_date'], new \DateTimeZone('UTC'))
            );
        }

        // We don't know who this is so search Milex
        $uniqueIdentifierFields = $this->fieldModel->getUniqueIdentifierFields(['object' => $internalObjectName]);
        $identifiers            = [];

        foreach ($uniqueIdentifierFields as $field => $fieldLabel) {
            try {
                $integrationField = $mappingManualDAO->getIntegrationMappedField($integrationObjectDAO->getObject(), $internalObjectName, $field);
                if ($integrationValue = $integrationObjectDAO->getField($integrationField)) {
                    $identifiers[$field] = $integrationValue->getValue()->getNormalizedValue();
                }
            } catch (FieldNotFoundException $e) {
            }
        }

        if (empty($identifiers)) {
            // No fields found to search for contact so return null
            return new ObjectDAO($internalObjectName, null);
        }

        try {
            $event = new InternalObjectFindEvent(
                $this->objectProvider->getObjectByName($internalObjectName)
            );
        } catch (ObjectNotFoundException $e) {
            // Throw this exception for BC.
            throw new ObjectNotSupportedException(MilexSyncDataExchange::NAME, $internalObjectName);
        }

        $event->setFieldValues($identifiers);

        $this->dispatcher->dispatch(
            IntegrationEvents::INTEGRATION_FIND_INTERNAL_RECORDS,
            $event
        );

        $foundObjects = $event->getFoundObjects();

        if (!$foundObjects) {
            // No contacts were found
            return new ObjectDAO($internalObjectName, null);
        }

        // Match found!
        $objectId = $foundObjects[0]['id'];

        // Let's store the relationship since we know it
        $objectMapping = new ObjectMapping();
        $objectMapping->setLastSyncDate($integrationObjectDAO->getChangeDateTime())
            ->setIntegration($mappingManualDAO->getIntegration())
            ->setIntegrationObjectName($integrationObjectDAO->getObject())
            ->setIntegrationObjectId($integrationObjectDAO->getObjectId())
            ->setInternalObjectName($internalObjectName)
            ->setInternalObjectId($objectId);
        $this->saveObjectMapping($objectMapping);

        return new ObjectDAO($internalObjectName, $objectId);
    }

    /**
     * Returns corresponding Milex entity class name for the given Milex object.
     *
     * @throws ObjectNotSupportedException
     */
    public function getMilexEntityClassName(string $internalObject): string
    {
        try {
            return $this->objectProvider->getObjectByName($internalObject)->getEntityName();
        } catch (ObjectNotFoundException $e) {
            // Throw this exception instead to keep BC.
            throw new ObjectNotSupportedException(MilexSyncDataExchange::NAME, $internalObject);
        }
    }

    /**
     * @return ObjectDAO
     *
     * @throws ObjectDeletedException
     */
    public function findIntegrationObject(string $integration, string $integrationObjectName, ObjectDAO $internalObjectDAO)
    {
        if ($integrationObject = $this->objectMappingRepository->getIntegrationObject(
            $integration,
            $internalObjectDAO->getObject(),
            $internalObjectDAO->getObjectId(),
            $integrationObjectName
        )) {
            if ($integrationObject['is_deleted']) {
                throw new ObjectDeletedException();
            }

            return new ObjectDAO(
                $integrationObjectName,
                $integrationObject['integration_object_id'],
                new \DateTime($integrationObject['last_sync_date'], new \DateTimeZone('UTC'))
            );
        }

        return new ObjectDAO($integrationObjectName, null);
    }

    /**
     * @param ObjectMapping[] $mappings
     */
    public function saveObjectMappings(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            $this->saveObjectMapping($mapping);
        }
    }

    public function updateObjectMappings(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            try {
                $this->updateObjectMapping($mapping);
            } catch (ObjectNotFoundException $exception) {
                continue;
            }
        }
    }

    /**
     * @param RemappedObjectDAO[] $mappings
     */
    public function remapIntegrationObjects(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            $this->objectMappingRepository->updateIntegrationObject(
                $mapping->getIntegration(),
                $mapping->getOldObjectName(),
                $mapping->getOldObjectId(),
                $mapping->getNewObjectName(),
                $mapping->getNewObjectId()
            );
        }
    }

    /**
     * @param ObjectChangeDAO[] $objects
     */
    public function markAsDeleted(array $objects): void
    {
        foreach ($objects as $object) {
            $this->objectMappingRepository->markAsDeleted($object->getIntegration(), $object->getObject(), $object->getObjectId());
        }
    }

    private function saveObjectMapping(ObjectMapping $objectMapping): void
    {
        $this->objectMappingRepository->saveEntity($objectMapping);
        $this->objectMappingRepository->clear();
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function updateObjectMapping(UpdatedObjectMappingDAO $updatedObjectMappingDAO): void
    {
        /** @var ObjectMapping $objectMapping */
        $objectMapping = $this->objectMappingRepository->findOneBy(
            [
                'integration'           => $updatedObjectMappingDAO->getIntegration(),
                'integrationObjectName' => $updatedObjectMappingDAO->getIntegrationObjectName(),
                'integrationObjectId'   => $updatedObjectMappingDAO->getIntegrationObjectId(),
            ]
        );

        if (!$objectMapping) {
            throw new ObjectNotFoundException($updatedObjectMappingDAO->getIntegrationObjectName().':'.$updatedObjectMappingDAO->getIntegrationObjectId());
        }

        $objectMapping->setLastSyncDate($updatedObjectMappingDAO->getObjectModifiedDate());

        $this->saveObjectMapping($objectMapping);
    }
}
