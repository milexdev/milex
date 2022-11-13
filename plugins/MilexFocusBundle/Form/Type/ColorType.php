<?php

namespace MilexPlugin\MilexFocusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'primary',
            TextType::class,
            [
                'label'      => 'milex.focus.form.primary_color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'onchange'    => 'Milex.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'text',
            TextType::class,
            [
                'label'      => 'milex.focus.form.text_color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'onchange'    => 'Milex.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'button',
            TextType::class,
            [
                'label'      => 'milex.focus.form.button_color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'onchange'    => 'Milex.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'button_text',
            TextType::class,
            [
                'label'      => 'milex.focus.form.button_text_color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'onchange'    => 'Milex.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'focus_color';
    }
}
