<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Form\Type;

use Milex\IntegrationsBundle\Exception\InvalidFormOptionException;
use Milex\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use Milex\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
use Milex\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class IntegrationSyncSettingsObjectFieldMappingType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    public function __construct(TranslatorInterface $translator, FieldHelper $fieldHelper)
    {
        $this->translator  = $translator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @throws InvalidFormOptionException
     * @throws ObjectNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $integrationFields = $options['integrationFields'];

        /** @var ConfigFormSyncInterface $integrationObject */
        $integrationObject = $options['integrationObject'];
        if (!$integrationObject instanceof ConfigFormSyncInterface) {
            throw new InvalidFormOptionException('integrationObject must be an instance of ConfigFormSyncInterface');
        }

        $objectName = $options['object'];
        foreach ($integrationFields as $fieldName => $fieldInfo) {
            if (!$fieldInfo instanceof MappedFieldInfoInterface) {
                throw new InvalidFormOptionException('integrationFields must contain an instance of MappedFieldInfoInterface');
            }

            $attr = [
                'label'        => $fieldInfo->getLabel(),
                'milexFields' => $this->getMilexFields($integrationObject, $objectName),
                'required'     => $fieldInfo->showAsRequired(),
                'placeholder'  => $this->translator->trans('milex.integration.sync_milex_field'),
                'object'       => $objectName,
                'integration'  => $integrationObject->getName(),
                'field'        => $fieldInfo,
            ];

            if ($fieldInfo->hasTooltip()) {
                $attr['attr'] = [
                    'tooltip' => $fieldInfo->getTooltip(),
                    'class'   => 'form-control',
                ];
            }

            $builder->add(
                $fieldName,
                IntegrationSyncSettingsObjectFieldType::class,
                $attr
            );
        }

        $builder->add(
            'filter-keyword',
            TextType::class,
            [
                'label'  => false,
                'mapped' => false,
                'data'   => $options['keyword'],
                'attr'   => [
                    'class'            => 'form-control integration-keyword-filter',
                    'placeholder'      => $this->translator->trans('milex.integration.sync_filter_fields'),
                    'data-object'      => $objectName,
                    'data-integration' => $integrationObject->getName(),
                ],
            ]
        );

        $builder->add(
            'filter-totalFieldCount',
            HiddenType::class,
            [
                'label'  => false,
                'mapped' => false,
                'data'   => $options['totalFieldCount'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'integrationFields',
                'page',
                'keyword',
                'totalFieldCount',
                'object',
                'integrationObject',
            ]
        );
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function getMilexFields(ConfigFormSyncInterface $integrationObject, string $objectName): array
    {
        $mappedObjects = $integrationObject->getSyncMappedObjects();
        if (!isset($mappedObjects[$objectName])) {
            throw new ObjectNotFoundException($objectName);
        }

        $milexObject = $mappedObjects[$objectName];

        return $this->fieldHelper->getSyncFields($milexObject);
    }
}
