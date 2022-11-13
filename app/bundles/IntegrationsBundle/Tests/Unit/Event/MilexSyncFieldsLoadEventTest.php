<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Unit\Event;

use Milex\IntegrationsBundle\Event\MilexSyncFieldsLoadEvent;
use PHPUnit\Framework\TestCase;

class MilexSyncFieldsLoadEventTest extends TestCase
{
    public function testWorkflow(): void
    {
        $objectName = 'object';
        $fields     = [
            'fieldKey' => 'fieldName',
        ];

        $newFieldKey   = 'newFieldKey';
        $newFieldValue = 'newFieldValue';

        $event = new MilexSyncFieldsLoadEvent($objectName, $fields);
        $this->assertSame($objectName, $event->getObjectName());
        $this->assertSame($fields, $event->getFields());
        $event->addField($newFieldKey, $newFieldValue);
        $this->assertSame(
            array_merge($fields, [$newFieldKey => $newFieldValue]),
            $event->getFields()
        );
    }
}
