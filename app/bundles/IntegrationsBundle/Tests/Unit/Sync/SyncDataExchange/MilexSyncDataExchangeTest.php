<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Unit\Sync\SyncDataExchange;

use Milex\IntegrationsBundle\Entity\FieldChangeRepository;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Milex\IntegrationsBundle\Sync\Helper\MappingHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\LeadBundle\Entity\Lead;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MilexSyncDataExchangeTest extends TestCase
{
    /**
     * @var MockObject|FieldChangeRepository
     */
    private $fieldChangeRepository;

    /**
     * @var MockObject|FieldHelper
     */
    private $fieldHelper;

    /**
     * @var MockObject|MappingHelper
     */
    private $mappingHelper;

    /**
     * @var MockObject|FullObjectReportBuilder
     */
    private $fullObjectReportBuilder;

    /**
     * @var MockObject|PartialObjectReportBuilder
     */
    private $partialObjectReportBuilder;

    /**
     * @var MockObject|OrderExecutioner
     */
    private $orderExecutioner;

    /**
     * @var MilexSyncDataExchange
     */
    private $milexSyncDataExchange;

    protected function setUp(): void
    {
        $this->fieldChangeRepository      = $this->createMock(FieldChangeRepository::class);
        $this->fieldHelper                = $this->createMock(FieldHelper::class);
        $this->mappingHelper              = $this->createMock(MappingHelper::class);
        $this->fullObjectReportBuilder    = $this->createMock(FullObjectReportBuilder::class);
        $this->partialObjectReportBuilder = $this->createMock(PartialObjectReportBuilder::class);
        $this->orderExecutioner           = $this->createMock(OrderExecutioner::class);

        $this->milexSyncDataExchange = new MilexSyncDataExchange(
            $this->fieldChangeRepository,
            $this->fieldHelper,
            $this->mappingHelper,
            $this->fullObjectReportBuilder,
            $this->partialObjectReportBuilder,
            $this->orderExecutioner
        );
    }

    public function testFirstTimeSyncUsesFullObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration'     => 'foobar',
                'first-time-sync' => true,
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->milexSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testSyncingSpecificMilexIdsUseFullObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration'      => 'foobar',
                'milex-object-id' => [1, 2, 3],
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->milexSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testUseOfPartialObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration' => 'foobar',
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->milexSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testGetConflictedInternalObjectWithNoObjectId(): void
    {
        $mappingManualDao     = new MappingManualDAO('IntegrationA');
        $integrationObjectDao = new ObjectDAO('Lead', 'some-SF-ID');

        $this->mappingHelper->expects($this->once())
            ->method('findMilexObject')
            ->with($mappingManualDao, 'lead', $integrationObjectDao)
            ->willReturn(new ObjectDAO('lead', null));

        // No need to make the DB query when ID is null.
        $this->fieldChangeRepository->expects($this->never())
            ->method('findChangesForObject');

        $internalObjectDao = $this->milexSyncDataExchange->getConflictedInternalObject($mappingManualDao, 'lead', $integrationObjectDao);

        Assert::assertSame('lead', $internalObjectDao->getObject());
        Assert::assertNull($internalObjectDao->getObjectId());
    }

    public function testGetConflictedInternalObjectWithObjectId(): void
    {
        $mappingManualDao     = new MappingManualDAO('IntegrationA');
        $integrationObjectDao = new ObjectDAO('Lead', 'some-SF-ID');
        $fieldChange          = [
            'modified_at'  => '2020-08-25 17:20:00',
            'column_type'  => 'text',
            'column_value' => 'some-field-value',
            'column_name'  => 'some-field-name',
        ];

        $this->mappingHelper->expects($this->once())
            ->method('findMilexObject')
            ->with($mappingManualDao, 'lead', $integrationObjectDao)
            ->willReturn(new ObjectDAO('lead', 123));

        $this->mappingHelper->method('getMilexEntityClassName')
            ->with('lead')
            ->willReturn(Lead::class);

        $this->fieldHelper->method('getFieldChangeObject')
            ->with($fieldChange)
            ->willReturn(new FieldDAO('some-field-name', new NormalizedValueDAO('type', 'some-field-value')));

        $this->fieldChangeRepository->expects($this->once())
            ->method('findChangesForObject')
            ->with('IntegrationA', Lead::class, 123)
            ->willReturn([$fieldChange]);

        $internalObjectDao = $this->milexSyncDataExchange->getConflictedInternalObject($mappingManualDao, 'lead', $integrationObjectDao);

        Assert::assertSame('lead', $internalObjectDao->getObject());
        Assert::assertSame(123, $internalObjectDao->getObjectId());
        Assert::assertCount(1, $internalObjectDao->getFields());
    }
}
