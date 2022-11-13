<?php

namespace MilexPlugin\MilexCrmBundle\Tests\Api;

use MilexPlugin\MilexCrmBundle\Api\ConnectwiseApi;
use MilexPlugin\MilexCrmBundle\Integration\ConnectwiseIntegration;
use MilexPlugin\MilexCrmBundle\Tests\Integration\DataGeneratorTrait;

class ConnectwiseApiTest extends \PHPUnit\Framework\TestCase
{
    use DataGeneratorTrait;

    /**
     * @testdox Tests that fetchAllRecords loops until all records are obtained
     * @covers  \MilexPlugin\MilexCrmBundle\Api\ConnectwiseApi::fetchAllRecords()
     *
     * @throws \Milex\PluginBundle\Exception\ApiErrorException
     */
    public function testResultPagination()
    {
        $integration = $this->getMockBuilder(ConnectwiseIntegration::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getRecords'])
            ->getMock();

        $page = 0;
        $integration->expects($this->exactly(3))
            ->method('makeRequest')
            ->willReturnCallback(
                function ($endpoint, $parameters) use (&$page) {
                    ++$page;

                    // Page should be incremented 3 times by fetchAllRecords method
                    $this->assertEquals(['page' => $page, 'pageSize' => ConnectwiseIntegration::PAGESIZE], $parameters);

                    return $this->generateData(3);
                }
            );

        $api = new ConnectwiseApi($integration);

        $records = $api->fetchAllRecords('test');

        $this->assertEquals($this->generatedRecords, $records);
    }
}
