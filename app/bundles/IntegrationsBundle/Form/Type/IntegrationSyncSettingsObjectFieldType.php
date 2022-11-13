<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Form\Type;

use Milex\IntegrationsBundle\Exception\InvalidFormOptionException;
use Milex\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationSyncSettingsObjectFieldType extends AbstractType
{
    /**
     * @throws InvalidFormOptionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $field = $options['field'];
        if (!$field instanceof MappedFieldInfoInterface) {
            throw new InvalidFormOptionException('field must contain an instance of MappedFieldInfoInterface');
        }

        $builder->add(
            'mappedField',
            ChoiceType::class,
            [
                'label'          => false,
                'choices'        => array_flip($options['milexFields']),
                'required'       => $field->showAsRequired(),
                'placeholder'    => '',
                'error_bubbling' => false,
                'attr'           => [
                    'class'            => 'form-control integration-mapped-field',
                    'data-placeholder' => $options['placeholder'],
                    'data-object'      => $options['object'],
                    'data-integration' => $options['integration'],
                    'data-field'       => $field->getName(),
                ],
            ]
        );

        $choices = [];
        if ($field->isBidirectionalSyncEnabled()) {
            $choices['milex.integration.sync_direction_bidirectional'] = ObjectMappingDAO::SYNC_BIDIRECTIONALLY;
        }
        if ($field->isToIntegrationSyncEnabled()) {
            $choices['milex.integration.sync_direction_integration'] = ObjectMappingDAO::SYNC_TO_INTEGRATION;
        }
        if ($field->isToMilexSyncEnabled()) {
            $choices['milex.integration.sync_direction_milex'] = ObjectMappingDAO::SYNC_TO_MILEX;
        }

        if (empty($choices)) {
            throw new InvalidFormOptionException('field "'.$field->getName().'" must allow at least 1 direction for sync');
        }

        $defaultChoice = $choices[array_key_first($choices)];

        $builder->add(
            'syncDirection',
            ChoiceType::class,
            [
                'choices'    => $choices,
                'label'      => false,
                'empty_data' => $defaultChoice,
                'attr'       => [
                    'class'            => 'integration-sync-direction',
                    'data-object'      => $options['object'],
                    'data-integration' => $options['integration'],
                    'data-field'       => $field->getName(),
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'milexFields',
                'placeholder',
                'integration',
                'object',
                'field',
            ]
        );
    }
}
