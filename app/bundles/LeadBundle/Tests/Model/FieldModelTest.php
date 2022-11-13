<?php

namespace Milex\LeadBundle\Tests\Model;

use Milex\CoreBundle\Doctrine\Helper\ColumnSchemaHelper;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Entity\LeadFieldRepository;
use Milex\LeadBundle\Field\CustomFieldColumn;
use Milex\LeadBundle\Field\Dispatcher\FieldSaveDispatcher;
use Milex\LeadBundle\Field\FieldList;
use Milex\LeadBundle\Field\FieldsWithUniqueIdentifier;
use Milex\LeadBundle\Field\LeadFieldSaver;
use Milex\LeadBundle\Model\FieldModel;
use Milex\LeadBundle\Model\ListModel;

class FieldModelTest extends MilexMysqlTestCase
{
    protected $useCleanupRollback = false;

    public function testSingleContactFieldIsCreatedAndDeleted()
    {
        $fieldModel = self::$container->get('milex.lead.model.field');

        $field = new LeadField();
        $field->setName('Test Field')
            ->setAlias('test_field')
            ->setType('string')
            ->setObject('lead');

        $fieldModel->saveEntity($field);
        $fieldModel->deleteEntity($field);

        $this->assertCount(0, $this->getColumns('leads', $field->getAlias()));
    }

    public function testSingleCompanyFieldIsCreatedAndDeleted()
    {
        $fieldModel = self::$container->get('milex.lead.model.field');

        $field = new LeadField();
        $field->setName('Test Field')
            ->setAlias('test_field')
            ->setType('string')
            ->setObject('company');

        $fieldModel->saveEntity($field);
        $fieldModel->deleteEntity($field);

        $this->assertCount(0, $this->getColumns('companies', $field->getAlias()));
    }

    public function testMultipleFieldsAreCreatedAndDeleted()
    {
        $fieldModel = self::$container->get('milex.lead.model.field');

        $leadField = new LeadField();
        $leadField->setName('Test Field')
            ->setAlias('test_field')
            ->setType('string')
            ->setObject('lead');

        $leadField2 = new LeadField();
        $leadField2->setName('Test Field')
            ->setAlias('test_field2')
            ->setType('string')
            ->setObject('lead');

        $companyField = new LeadField();
        $companyField->setName('Test Field')
            ->setAlias('test_field')
            ->setType('string')
            ->setObject('company');

        $companyField2 = new LeadField();
        $companyField2->setName('Test Field')
            ->setAlias('test_field2')
            ->setType('string')
            ->setObject('company');

        $fieldModel->saveEntities([$leadField, $leadField2, $companyField, $companyField2]);

        $this->assertCount(1, $this->getColumns('leads', $leadField->getAlias()));
        $this->assertCount(1, $this->getColumns('leads', $leadField2->getAlias()));
        $this->assertCount(1, $this->getColumns('companies', $companyField->getAlias()));
        $this->assertCount(1, $this->getColumns('companies', $companyField2->getAlias()));

        $fieldModel->deleteEntities([$leadField->getId(), $leadField2->getId(), $companyField->getId(), $companyField2->getId()]);

        $this->assertCount(0, $this->getColumns('leads', $leadField->getAlias()));
        $this->assertCount(0, $this->getColumns('leads', $leadField2->getAlias()));
        $this->assertCount(0, $this->getColumns('companies', $companyField->getAlias()));
        $this->assertCount(0, $this->getColumns('companies', $companyField2->getAlias()));
    }

    public function testIsUsedField()
    {
        $leadField = new LeadField();

        $columnSchemaHelper         = $this->createMock(ColumnSchemaHelper::class);
        $leadListModel              = $this->createMock(ListModel::class);
        $customFieldColumn          = $this->createMock(CustomFieldColumn::class);
        $fieldSaveDispatcher        = $this->createMock(FieldSaveDispatcher::class);
        $leadFieldRepository        = $this->createMock(LeadFieldRepository::class);
        $fieldsWithUniqueIdentifier = $this->createMock(FieldsWithUniqueIdentifier::class);
        $fieldList                  = $this->createMock(FieldList::class);
        $leadFieldSaver             = $this->createMock(LeadFieldSaver::class);
        $leadListModel->expects($this->once())
            ->method('isFieldUsed')
            ->with($leadField)
            ->willReturn(true);

        $model = new FieldModel($columnSchemaHelper, $leadListModel, $customFieldColumn, $fieldSaveDispatcher, $leadFieldRepository, $fieldsWithUniqueIdentifier, $fieldList, $leadFieldSaver);
        $this->assertTrue($model->isUsedField($leadField));
    }

    /**
     * @param $table
     * @param $column
     *
     * @return array
     */
    private function getColumns($table, $column)
    {
        $stmt       = $this->connection->executeQuery(
            "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->connection->getDatabase()}' AND TABLE_NAME = '".MILEX_TABLE_PREFIX
            ."$table' AND COLUMN_NAME = '$column'"
        );

        return $stmt->fetchAll();
    }
}
