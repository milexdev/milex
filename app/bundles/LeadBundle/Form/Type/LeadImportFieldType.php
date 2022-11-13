<?php

namespace Milex\LeadBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\UserBundle\Entity\User;
use Milex\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class LeadImportFieldType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManager $entityManager)
    {
        $this->translator    = $translator;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach ($options['all_fields'] as $optionGroup => $fields) {
            $choices[$optionGroup] = array_flip($fields);
        }

        foreach ($options['import_fields'] as $field => $label) {
            $builder->add(
                $field,
                ChoiceType::class,
                [
                    'choices'    => $choices,
                    'label'      => $label,
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                    'data'       => $this->getDefaultValue($field, $options['import_fields']),
                ]
            );
        }

        $transformer = new IdToEntityModelTransformer($this->entityManager, User::class);

        $builder->add(
            $builder->create(
                'owner',
                UserListType::class,
                [
                    'label'      => 'milex.lead.lead.field.owner',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'required' => false,
                    'multiple' => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        if ('lead' === $options['object']) {
            $builder->add(
                $builder->create(
                    'list',
                    LeadListType::class,
                    [
                        'label'      => 'milex.lead.lead.field.list',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class' => 'form-control',
                        ],
                        'required' => false,
                        'multiple' => false,
                    ]
                )
            );

            $builder->add(
                'tags',
                TagType::class,
                [
                    'label'      => 'milex.lead.tags',
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'                => 'form-control',
                        'data-placeholder'     => $this->translator->trans('milex.lead.tags.select_or_create'),
                        'data-no-results-text' => $this->translator->trans('milex.lead.tags.enter_to_create'),
                        'data-allow-add'       => 'true',
                        'onchange'             => 'Milex.createLeadTag(this)',
                    ],
                ]
            );
        }

        $builder->add(
            'skip_if_exists',
            YesNoButtonGroupType::class,
            [
                'label'       => 'milex.lead.import.skip_if_exists',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => false,
                'data'        => false,
            ]
        );

        $buttons = ['cancel_icon' => 'fa fa-times'];

        if (empty($options['line_count_limit'])) {
            $buttons = array_merge(
                $buttons,
                [
                    'apply_text'  => 'milex.lead.import.in.background',
                    'apply_class' => 'btn btn-success',
                    'apply_icon'  => 'fa fa-history',
                    'save_text'   => 'milex.lead.import.start',
                    'save_class'  => 'btn btn-primary',
                    'save_icon'   => 'fa fa-upload',
                ]
            );
        } else {
            $buttons = array_merge(
                $buttons,
                [
                    'apply_text' => false,
                    'save_text'  => 'milex.lead.import',
                    'save_class' => 'btn btn-primary',
                    'save_icon'  => 'fa fa-upload',
                ]
            );
        }

        $builder->add('buttons', FormButtonsType::class, $buttons);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['all_fields', 'import_fields', 'object']);
        $resolver->setDefaults([
            'line_count_limit'  => 0,
            'validation_groups' => [
                User::class,
                'determineValidationGroups',
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'lead_field_import';
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    public function getDefaultValue($fieldName, array $importFields)
    {
        if (isset($importFields[$fieldName])) {
            return $importFields[$fieldName];
        }

        return null;
    }
}
