<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Field;

use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Entity\LeadFieldRepository;
use Milex\LeadBundle\Field\Dispatcher\FieldSaveDispatcher;
use Milex\LeadBundle\Field\LeadFieldSaver;

class LeadFieldSaverTest extends \PHPUnit\Framework\TestCase
{
    public function testSave(): void
    {
        $leadFieldRepository = $this->createMock(LeadFieldRepository::class);
        $fieldSaveDispatcher = $this->createMock(FieldSaveDispatcher::class);

        $leadFieldSaver = new LeadFieldSaver($leadFieldRepository, $fieldSaveDispatcher);

        $leadField = new LeadField();

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPreSaveEvent')
            ->with($leadField, true);

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPostSaveEvent')
            ->with($leadField, true);

        $leadFieldSaver->saveLeadFieldEntity($leadField, true);
    }

    public function testSaveNoColumnCreated(): void
    {
        $leadFieldRepository = $this->createMock(LeadFieldRepository::class);
        $fieldSaveDispatcher = $this->createMock(FieldSaveDispatcher::class);

        $leadFieldSaver = new LeadFieldSaver($leadFieldRepository, $fieldSaveDispatcher);

        $leadField = new LeadField();

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPreSaveEvent')
            ->with($leadField, true);

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPostSaveEvent')
            ->with($leadField, true);

        $leadFieldSaver->saveLeadFieldEntityWithoutColumnCreated($leadField);

        $this->assertTrue($leadField->getColumnIsNotCreated());
    }
}
