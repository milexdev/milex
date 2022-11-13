<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Unit\Sync\Helper;

use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\RelationsDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\RelationDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\ReferenceValueDAO;
use Milex\IntegrationsBundle\Sync\Helper\MappingHelper;
use Milex\IntegrationsBundle\Sync\Helper\RelationsHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use PHPUnit\Framework\TestCase;

class RelationsHelperTest extends TestCase
{
    /**
     * @var MappingHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mappingHelper;

    /**
     * @var RelationsHelper
     */
    private $relationsHelper;

    /**
     * @var ReportDAO|\PHPUnit\Framework\MockObject\MockObject
     */
    private $syncReport;

    /**
     * @var MappingManualDAO|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mappingManual;

    protected function setUp(): void
    {
        $this->mappingHelper   = $this->createMock(MappingHelper::class);
        $this->relationsHelper = new RelationsHelper($this->mappingHelper);
        $this->syncReport      = $this->createMock(ReportDAO::class);
        $this->mappingManual   = $this->createMock(MappingManualDAO::class);
    }

    public function testProcessRelationsWithUnsychronisedObjects(): void
    {
        $integrationObjectId    = 'IntegrationId-123';
        $integrationRelObjectId = 'IntegrationId-456';
        $relObjectName          = 'Account';

        $relationObject = new RelationDAO(
            'Contact',
            'AccountId',
            $relObjectName,
            $integrationObjectId,
            $integrationRelObjectId
        );

        $relationsObject = new RelationsDAO();
        $relationsObject->addRelation($relationObject);

        $this->syncReport->expects($this->once())
            ->method('getRelations')
            ->willReturn($relationsObject);

        $this->mappingManual->expects($this->any())
            ->method('getMappedInternalObjectsNames')
            ->willReturn(['company']);

        $internalObject = new ObjectDAO('company', null);

        $this->mappingHelper->expects($this->once())
            ->method('findMilexObject')
            ->willReturn($internalObject);

        $this->relationsHelper->processRelations($this->mappingManual, $this->syncReport);

        $objectsToSynchronize = $this->relationsHelper->getObjectsToSynchronize();

        $this->assertCount(1, $objectsToSynchronize);

        $this->assertEquals($objectsToSynchronize[0]->getObjectId(), $integrationRelObjectId);
        $this->assertEquals($objectsToSynchronize[0]->getObject(), $relObjectName);
    }

    public function testProcessRelationsWithSychronisedObjects(): void
    {
        $integrationObjectId    = 'IntegrationId-123';
        $integrationRelObjectId = 'IntegrationId-456';
        $internalRelObjectId    = 13;
        $relObjectName          = 'Account';
        $relFieldName           = 'AccountId';

        $referenceVlaue  = new ReferenceValueDAO();
        $normalizedValue = new NormalizedValueDAO(NormalizedValueDAO::REFERENCE_TYPE, $integrationRelObjectId, $referenceVlaue);

        $fieldDao  = new FieldDAO('AccountId', $normalizedValue);
        $objectDao = new ObjectDAO('Contact', 1);
        $objectDao->addField($fieldDao);

        $relationObject = new RelationDAO(
            'Contact',
            $relFieldName,
            $relObjectName,
            $integrationObjectId,
            $integrationRelObjectId
        );

        $relationsObject = new RelationsDAO();
        $relationsObject->addRelation($relationObject);

        $this->syncReport->expects($this->once())
            ->method('getRelations')
            ->willReturn($relationsObject);

        $this->syncReport->expects($this->once())
            ->method('getObject')
            ->willReturn($objectDao);

        $this->mappingManual->expects($this->any())
            ->method('getMappedInternalObjectsNames')
            ->willReturn(['company']);

        $internalObject = new ObjectDAO(MilexSyncDataExchange::OBJECT_COMPANY, $internalRelObjectId);

        $this->mappingHelper->expects($this->once())
            ->method('findMilexObject')
            ->willReturn($internalObject);

        $this->relationsHelper->processRelations($this->mappingManual, $this->syncReport);

        $objectsToSynchronize = $this->relationsHelper->getObjectsToSynchronize();

        $this->assertCount(0, $objectsToSynchronize);
        $this->assertEquals($internalRelObjectId, $objectDao->getField($relFieldName)->getValue()->getNormalizedValue()->getValue());
        $this->assertEquals(MilexSyncDataExchange::OBJECT_COMPANY, $objectDao->getField($relFieldName)->getValue()->getNormalizedValue()->getType());
    }
}
