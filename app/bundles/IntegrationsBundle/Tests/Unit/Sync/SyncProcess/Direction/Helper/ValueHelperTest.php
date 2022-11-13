<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Unit\Sync\SyncProcess\Direction\Helper;

use Milex\IntegrationsBundle\Exception\InvalidValueException;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Milex\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper;
use PHPUnit\Framework\TestCase;

class ValueHelperTest extends TestCase
{
    public function testExceptionForMissingRequiredIntegrationValue(): void
    {
        $this->expectException(InvalidValueException::class);

        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_REQUIRED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );
    }

    public function testNoExceptionForMissingNonRequiredIntegrationValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_MILEX
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testNoExceptionForMissingOppositeSyncIntegrationValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testExceptionForMissingRequiredMilexValue(): void
    {
        $this->expectException(InvalidValueException::class);

        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $this->getValueHelper()->getValueForMilex(
            $normalizedValueDAO,
            FieldDAO::FIELD_REQUIRED,
            ObjectMappingDAO::SYNC_TO_MILEX
        );
    }

    public function testNoExceptionForMissingNonRequiredInternalValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForMilex(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testNoExceptionForMissingOppositeSyncInternalnValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForMilex(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_MILEX
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    /**
     * @return ValueHelper
     */
    private function getValueHelper()
    {
        return new ValueHelper();
    }
}
