<?php

namespace MilexPlugin\MilexFocusBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FocusPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];

        // Type specific
        switch ($options['focus_style']) {
            case 'bar':
                $builder->add(
                    'allow_hide',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'milex.focus.form.bar.allow_hide',
                        'data'  => (isset($options['data']['allow_hide'])) ? $options['data']['allow_hide'] : true,
                        'attr'  => [
                            'onchange' => 'Milex.focusUpdatePreview()',
                        ],
                    ]
                );

                $builder->add(
                    'push_page',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'milex.focus.form.bar.push_page',
                        'attr'  => [
                            'tooltip'  => 'milex.focus.form.bar.push_page.tooltip',
                            'onchange' => 'Milex.focusUpdatePreview()',
                        ],
                        'data' => (isset($options['data']['push_page'])) ? $options['data']['push_page'] : true,
                    ]
                );

                $builder->add(
                    'sticky',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'milex.focus.form.bar.sticky',
                        'attr'  => [
                            'tooltip'  => 'milex.focus.form.bar.sticky.tooltip',
                            'onchange' => 'Milex.focusUpdatePreview()',
                        ],
                        'data' => (isset($options['data']['sticky'])) ? $options['data']['sticky'] : true,
                    ]
                );

                $builder->add(
                    'size',
                    ChoiceType::class,
                    [
                        'choices'           => [
                            'milex.focus.form.bar.size.large'   => 'large',
                            'milex.focus.form.bar.size.regular' => 'regular',
                        ],
                        'label'      => 'milex.focus.form.bar.size',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'    => 'form-control',
                            'onchange' => 'Milex.focusUpdatePreview()',
                        ],
                        'required'    => false,
                        'placeholder' => false,
                    ]
                );

                $choices = [
                    'milex.focus.form.placement.top'    => 'top',
                    'milex.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'modal':
                $choices = [
                    'milex.focus.form.placement.top'    => 'top',
                    'milex.focus.form.placement.middle' => 'middle',
                    'milex.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'notification':
                $choices = [
                    'milex.focus.form.placement.top_left'     => 'top_left',
                    'milex.focus.form.placement.top_right'    => 'top_right',
                    'milex.focus.form.placement.bottom_left'  => 'bottom_left',
                    'milex.focus.form.placement.bottom_right' => 'bottom_right',
                ];
                break;
            case 'page':
                break;
        }

        if (!empty($choices)) {
            $builder->add(
                'placement',
                ChoiceType::class,
                [
                    'choices'           => $choices,
                    'label'             => 'milex.focus.form.placement',
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'class'    => 'form-control',
                        'onchange' => 'Milex.focusUpdatePreview()',
                    ],
                    'required'    => false,
                    'placeholder' => false,
                ]
            );
        }
    }

    public function getBlockPrefix()
    {
        return 'focus_properties';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['focus_style']);

        $resolver->setDefaults(
            [
                'label' => false,
            ]
        );
    }
}
