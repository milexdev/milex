<?php

namespace Milex\FormBundle\Form\Type;

use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FieldType.
 */
class FieldType extends AbstractType
{
    use FormFieldTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FieldType constructor.
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Populate settings
        $cleanMasks = [
            'labelAttributes'     => 'string',
            'inputAttributes'     => 'string',
            'containerAttributes' => 'string',
            'label'               => 'strict_html',
        ];

        $addHelpMessage         =
        $addShowLabel           =
        $allowCustomAlias       =
        $addDefaultValue        =
        $addLabelAttributes     =
        $addInputAttributes     =
        $addContainerAttributes =
        $addLeadFieldList       =
        $addSaveResult          =
        $addBehaviorFields      =
        $addIsRequired          = true;

        if (!empty($options['customParameters'])) {
            $type = 'custom';

            $customParams    = $options['customParameters'];
            $formTypeOptions = [
                'required' => false,
                'label'    => false,
            ];
            if (!empty($customParams['formTypeOptions'])) {
                $formTypeOptions = array_merge($formTypeOptions, $customParams['formTypeOptions']);
            }

            $addFields = [
                'labelText',
                'addHelpMessage',
                'addShowLabel',
                'labelText',
                'addDefaultValue',
                'addLabelAttributes',
                'labelAttributesText',
                'addInputAttributes',
                'inputAttributesText',
                'addContainerAttributes',
                'containerAttributesText',
                'addLeadFieldList',
                'addSaveResult',
                'addBehaviorFields',
                'addIsRequired',
                'addHtml',
            ];
            foreach ($addFields as $f) {
                if (isset($customParams['builderOptions'][$f])) {
                    $$f = (bool) $customParams['builderOptions'][$f];
                }
            }
        } else {
            $type = $options['data']['type'];
            switch ($type) {
                case 'freetext':
                    $addHelpMessage      = $addDefaultValue      = $addIsRequired      = $addLeadFieldList      = $addSaveResult      = $addBehaviorFields      = false;
                    $labelText           = 'milex.form.field.form.header';
                    $showLabelText       = 'milex.form.field.form.showheader';
                    $inputAttributesText = 'milex.form.field.form.freetext_attributes';
                    $labelAttributesText = 'milex.form.field.form.header_attributes';

                    // Allow html
                    $cleanMasks['properties'] = 'html';
                    break;
                case 'freehtml':
                    $addHelpMessage      = $addDefaultValue      = $addIsRequired      = $addLeadFieldList      = $addSaveResult      = $addBehaviorFields      = false;
                    $labelText           = 'milex.form.field.form.header';
                    $showLabelText       = 'milex.form.field.form.showheader';
                    $inputAttributesText = 'milex.form.field.form.freehtml_attributes';
                    $labelAttributesText = 'milex.form.field.form.header_attributes';
                    // Allow html
                    $cleanMasks['properties'] = 'html';
                    break;
                case 'button':
                    $addHelpMessage = $addShowLabel = $addDefaultValue = $addLabelAttributes = $addIsRequired = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    break;
                case 'hidden':
                    $addHelpMessage = $addShowLabel = $addLabelAttributes = $addIsRequired = false;
                    break;
                case 'captcha':
                    $addShowLabel = $addIsRequired = $addDefaultValue = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    break;
                case 'pagebreak':
                    $addShowLabel = $allowCustomAlias = $addHelpMessage = $addIsRequired = $addDefaultValue = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    break;
                case 'select':
                    $cleanMasks['properties']['list']['list']['label'] = 'strict_html';
                    break;
                case 'checkboxgrp':
                case 'radiogrp':
                    $cleanMasks['properties']['optionlist']['list']['label'] = 'strict_html';
                    break;
                case 'file':
                    $addShowLabel = $addDefaultValue = $addBehaviorFields = false;
                    break;
            }
        }

        // disable progressing profiling  for conditional fields
        if (!empty($options['data']['parent'])) {
            $addBehaviorFields = false;
            $builder->add(
                'conditions',
                FormFieldConditionType::class,
                [
                    'label'      => false,
                    'data'       => isset($options['data']['conditions']) ? $options['data']['conditions'] : [],
                    'formId'     => $options['data']['formId'],
                    'parent'     => isset($options['data']['parent']) ? $options['data']['parent'] : null,
                ]
            );
        }

        $builder->add(
            'parent',
            HiddenType::class,
            [
                'label'=> false,
            ]
        );

        // Build form fields
        $builder->add(
            'label',
            TextType::class,
            [
                'label'       => !empty($labelText) ? $labelText : 'milex.form.field.form.label',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(
                        ['message' => 'milex.form.field.label.notblank']
                    ),
                ],
            ]
        );

        if ($allowCustomAlias) {
            $builder->add(
                'alias',
                TextType::class,
                [
                    'label'      => 'milex.form.field.form.alias',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.form.field.form.alias.tooltip',
                    ],
                    'disabled' => (!empty($options['data']['id']) && false === strpos($options['data']['id'], 'new')) ? true : false,
                    'required' => false,
                ]
            );
        }

        if ($addShowLabel) {
            $default = (!isset($options['data']['showLabel'])) ? true : (bool) $options['data']['showLabel'];
            $builder->add(
                'showLabel',
                YesNoButtonGroupType::class,
                [
                    'label' => (!empty($showLabelText)) ? $showLabelText : 'milex.form.field.form.showlabel',
                    'data'  => $default,
                ]
            );
        }

        if ($addDefaultValue) {
            $builder->add(
                'defaultValue',
                ('textarea' == $type) ? TextareaType::class : TextType::class,
                [
                    'label'      => 'milex.core.defaultvalue',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                    'required'   => false,
                ]
            );
        }

        if ($addHelpMessage) {
            $builder->add(
                'helpMessage',
                TextType::class,
                [
                    'label'      => 'milex.form.field.form.helpmessage',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.form.field.help.helpmessage',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addIsRequired) {
            $default = (!isset($options['data']['isRequired'])) ? false : (bool) $options['data']['isRequired'];
            $builder->add(
                'isRequired',
                YesNoButtonGroupType::class,
                [
                    'label' => 'milex.core.required',
                    'data'  => $default,
                ]
            );

            $builder->add(
                'validationMessage',
                TextType::class,
                [
                    'label'      => 'milex.form.field.form.validationmsg',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'tooltip'      => $this->translator->trans('milex.core.form.default').': '.$this->translator->trans('milex.form.field.generic.required', [], 'validators'),
                        'data-show-on' => '{"formfield_isRequired_1": "checked"}',
                    ],
                    'required'   => false,
                ]
            );
        }

        if ($addLabelAttributes) {
            $builder->add(
                'labelAttributes',
                TextType::class,
                [
                    'label'      => (!empty($labelAttributesText)) ? $labelAttributesText : 'milex.form.field.form.labelattr',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'     => 'form-control',
                        'tooltip'   => 'milex.form.field.help.attr',
                        'maxlength' => '191',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addInputAttributes) {
            $builder->add(
                'inputAttributes',
                TextType::class,
                [
                    'label'      => (!empty($inputAttributesText)) ? $inputAttributesText : 'milex.form.field.form.inputattr',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'     => 'form-control',
                        'tooltip'   => 'milex.form.field.help.attr',
                        'maxlength' => '191',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addContainerAttributes) {
            $builder->add(
                'containerAttributes',
                TextType::class,
                [
                    'label'      => 'milex.form.field.form.container_attr',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'     => 'form-control',
                        'tooltip'   => 'milex.form.field.help.container_attr',
                        'maxlength' => '191',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addSaveResult) {
            $default = (!isset($options['data']['saveResult']) || null === $options['data']['saveResult']) ? true
                : (bool) $options['data']['saveResult'];
            $builder->add(
                'saveResult',
                YesNoButtonGroupType::class,
                [
                    'label' => 'milex.form.field.form.saveresult',
                    'data'  => $default,
                    'attr'  => [
                        'tooltip' => 'milex.form.field.help.saveresult',
                    ],
                ]
            );
        }

        if ($addBehaviorFields) {
            $alwaysDisplay = isset($options['data']['alwaysDisplay']) ? $options['data']['alwaysDisplay'] : false;
            $builder->add(
                'alwaysDisplay',
                YesNoButtonGroupType::class,
                [
                    'label' => 'milex.form.field.form.always_display',
                    'attr'  => [
                        'tooltip' => 'milex.form.field.form.always_display.tooltip',
                    ],
                    'data'  => $alwaysDisplay,
                ]
            );

            $default = (!isset($options['data']['showWhenValueExists']) || null === $options['data']['showWhenValueExists']) ? true
                : (bool) $options['data']['showWhenValueExists'];
            $builder->add(
                'showWhenValueExists',
                YesNoButtonGroupType::class,
                [
                    'label' => 'milex.form.field.form.show.when.value.exists',
                    'data'  => $default,
                    'attr'  => [
                        'tooltip'      => 'milex.form.field.help.show.when.value.exists',
                        'data-show-on' => '{"formfield_alwaysDisplay_0": "checked"}',
                    ],
                ]
            );

            $builder->add(
                'showAfterXSubmissions',
                TextType::class,
                [
                    'label'      => 'milex.form.field.form.show.after.x.submissions',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'tooltip'      => 'milex.form.field.help.show.after.x.submissions',
                        'data-show-on' => '{"formfield_alwaysDisplay_0": "checked"}',
                    ],
                    'required' => false,
                ]
            );

            $isAutoFillValue = (!isset($options['data']['isAutoFill'])) ? false : (bool) $options['data']['isAutoFill'];
            $builder->add(
                'isAutoFill',
                YesNoButtonGroupType::class,
                [
                    'label' => 'milex.form.field.form.auto_fill',
                    'data'  => $isAutoFillValue,
                    'attr'  => [
                        'class'   => 'auto-fill-data',
                        'tooltip' => 'milex.form.field.help.auto_fill',
                    ],
                ]
            );
        }

        if ($addLeadFieldList) {
            if (!isset($options['data']['leadField'])) {
                switch ($type) {
                    case 'email':
                        $data = 'email';
                        break;
                    case 'country':
                        $data = 'country';
                        break;
                    case 'tel':
                        $data = 'phone';
                        break;
                    default:
                        $data = '';
                        break;
                }
            } elseif (isset($options['data']['leadField'])) {
                $data = $options['data']['leadField'];
            } else {
                $data = '';
            }

            $builder->add(
                'leadField',
                ChoiceType::class,
                [
                    'choices'           => $options['leadFields'],
                    'choice_attr'       => function ($val, $key, $index) use ($options) {
                        $objects = ['lead', 'company'];
                        foreach ($objects as $object) {
                            if (!empty($options['leadFieldProperties'][$object][$val]) && (in_array($options['leadFieldProperties'][$object][$val]['type'], FormFieldHelper::getListTypes()) || !empty($options['leadFieldProperties'][$object][$val]['properties']['list']) || !empty($options['leadFieldProperties'][$object][$val]['properties']['optionlist']))) {
                                return ['data-list-type' => 1];
                            }
                        }

                        return [];
                    },
                    'label'      => 'milex.form.field.form.lead_field',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.form.field.help.lead_field',
                    ],
                    'required' => false,
                    'data'     => $data,
                ]
            );
        }

        $builder->add('type', HiddenType::class);

        $update = (!empty($options['data']['id'])) ? true : false;
        if (!empty($update)) {
            $btnValue = 'milex.core.form.update';
            $btnIcon  = 'fa fa-pencil';
        } else {
            $btnValue = 'milex.core.form.add';
            $btnIcon  = 'fa fa-plus';
        }

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'save_text'       => $btnValue,
                'save_icon'       => $btnIcon,
                'apply_text'      => false,
                'container_class' => 'bottom-form-buttons',
            ]
        );

        $builder->add(
            'formId',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        // Put properties last so that the other values are available to form events
        $propertiesData = (isset($options['data']['properties'])) ? $options['data']['properties'] : [];
        if (!empty($options['customParameters'])) {
            $formTypeOptions = array_merge($formTypeOptions, ['data' => $propertiesData]);
            $builder->add('properties', $customParams['formType'], $formTypeOptions);
        } else {
            switch ($type) {
                case 'select':
                case 'country':
                    $builder->add(
                        'properties',
                        FormFieldSelectType::class,
                        [
                            'field_type' => $type,
                            'label'      => false,
                            'parentData' => $options['data'],
                            'data'       => $propertiesData,
                        ]
                    );
                    break;
                case 'checkboxgrp':
                case 'radiogrp':
                    $builder->add(
                        'properties',
                        FormFieldGroupType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'freetext':
                    $builder->add(
                        'properties',
                        FormFieldTextType::class,
                        [
                            'required' => false,
                            'label'    => false,
                            'editor'   => true,
                            'data'     => $propertiesData,
                        ]
                    );
                    break;
                case 'freehtml':
                    $builder->add(
                        'properties',
                        FormFieldHTMLType::class,
                        [
                            'required' => false,
                            'label'    => false,
                            'editor'   => true,
                            'data'     => $propertiesData,
                        ]
                    );
                    break;
                case 'date':
                case 'email':
                case 'number':
                case 'text':
                case 'url':
                case 'tel':
                    $builder->add(
                        'properties',
                        FormFieldPlaceholderType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'captcha':
                    $builder->add(
                        'properties',
                        FormFieldCaptchaType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'pagebreak':
                    $builder->add(
                        'properties',
                        FormFieldPageBreakType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'file':
                    if (!isset($propertiesData['public'])) {
                        $propertiesData['public'] = false;
                    }
                    $builder->add(
                        'properties',
                        FormFieldFileType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
            }
        }

        $builder->addEventSubscriber(new CleanFormSubscriber($cleanMasks));

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'customParameters' => false,
            ]
        );

        $resolver->setDefined(['customParameters', 'leadFieldProperties']);

        $resolver->setRequired(['leadFields']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'formfield';
    }
}
