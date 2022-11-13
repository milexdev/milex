<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Unit\Form\Type;

use Milex\IntegrationsBundle\Exception\InvalidFormOptionException;
use Milex\IntegrationsBundle\Form\Type\IntegrationSyncSettingsObjectFieldType;
use Milex\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class IntegrationSyncSettingsObjectFieldTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var IntegrationSyncSettingsObjectFieldType
     */
    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->form        = new IntegrationSyncSettingsObjectFieldType();
    }

    public function testBuildFormForWrongField(): void
    {
        $options = ['field' => 'unicorn'];
        $this->expectException(InvalidFormOptionException::class);
        $this->form->buildForm($this->formBuilder, $options);
    }

    public function testBuildFormForMappedField(): void
    {
        $field   = $this->createMock(MappedFieldInfoInterface::class);
        $options = [
            'field'        => $field,
            'placeholder'  => 'Placeholder ABC',
            'object'       => 'Object A',
            'integration'  => 'Integration A',
            'milexFields' => [
                'milex_field_a' => 'Milex Field A',
                'milex_field_b' => 'Milex Field B',
            ],
        ];

        $field->method('showAsRequired')->willReturn(true);
        $field->method('getName')->willReturn('Integration Field A');
        $field->method('isBidirectionalSyncEnabled')->willReturn(false);
        $field->method('isToIntegrationSyncEnabled')->willReturn(true);
        $field->method('isToMilexSyncEnabled')->willReturn(true);

        $this->formBuilder->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    'mappedField',
                    ChoiceType::class,
                    [
                        'label'          => false,
                        'choices'        => [
                            'Milex Field A' => 'milex_field_a',
                            'Milex Field B' => 'milex_field_b',
                        ],
                        'required'       => true,
                        'placeholder'    => '',
                        'error_bubbling' => false,
                        'attr'           => [
                            'class'            => 'form-control integration-mapped-field',
                            'data-placeholder' => $options['placeholder'],
                            'data-object'      => $options['object'],
                            'data-integration' => $options['integration'],
                            'data-field'       => 'Integration Field A',
                        ],
                    ],
                ],
                [
                    'syncDirection',
                    ChoiceType::class,
                    [
                        'choices' => [
                            'milex.integration.sync_direction_integration' => ObjectMappingDAO::SYNC_TO_INTEGRATION,
                            'milex.integration.sync_direction_milex'      => ObjectMappingDAO::SYNC_TO_MILEX,
                        ],
                        'label'      => false,
                        'empty_data' => ObjectMappingDAO::SYNC_TO_INTEGRATION,
                        'attr'       => [
                            'class'            => 'integration-sync-direction',
                            'data-object'      => 'Object A',
                            'data-integration' => 'Integration A',
                            'data-field'       => 'Integration Field A',
                        ],
                    ],
                ]
            );

        $this->form->buildForm($this->formBuilder, $options);
    }
}
