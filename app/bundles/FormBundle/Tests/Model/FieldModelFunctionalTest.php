<?php

namespace Milex\FormBundle\Tests\Model;

use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Entity\LeadFieldRepository;

class FieldModelFunctionalTest extends MilexMysqlTestCase
{
    public function testGetObjectFieldsUnpublishedField(): void
    {
        /** @var \Milex\FormBundle\Model\FieldModel $fieldModel */
        $fieldModel   = self::$container->get('milex.form.model.field');
        $fieldsBefore = $fieldModel->getObjectFields('lead');

        /** @var LeadFieldRepository $leadFieldRepository */
        $leadFieldRepository = $this->em->getRepository(LeadField::class);
        $field               = $leadFieldRepository->findOneBy(['alias' => 'firstname']);
        $field->setIsPublished(false);
        $leadFieldRepository->saveEntity($field);

        $fieldsAfter = $fieldModel->getObjectFields('lead');

        self::assertTrue(array_key_exists('firstname', array_flip($fieldsBefore[1]['Core'])));
        self::assertFalse(array_key_exists('firstname', array_flip($fieldsAfter[1]['Core'])));
    }
}
