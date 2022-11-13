<?php

namespace MilexPlugin\MilexFocusBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'headline',
            TextType::class,
            [
                'label'      => 'milex.focus.form.headline',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'onblur'       => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'tagline',
            TextType::class,
            [
                'label'      => 'milex.focus.form.tagline',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'placeholder'  => 'milex.core.optional',
                    'onblur'       => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'link_text',
            TextType::class,
            [
                'label'      => 'milex.focus.form.link_text',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'onblur'       => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
            ]
        );

        $builder->add(
            'link_url',
            TextType::class,
            [
                'label'      => 'milex.focus.form.link_url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'onblur'       => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
            ]
        );

        $builder->add(
            'link_new_window',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.focus.form.link_new_window',
                'data'  => (isset($options['link_new_window'])) ? $options['link_new_window'] : true,
                'attr'  => [
                    'onchange'     => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
            ]
        );

        $builder->add(
            'font',
            ChoiceType::class,
            [
                'choices'           => [
                    'Arial'                    => 'Arial, Helvetica, sans-serif',
                    'Arial Black'              => '\'Arial Black\', Gadget, sans-serif',
                    'Arial Narrow'             => '\'Arial Narrow\', sans-serif',
                    'Century Gothic'           => 'Century Gothic, sans-serif',
                    'Copperplate Gothic Light' => 'Copperplate / Copperplate Gothic Light, sans-serif',
                    'Courier New'              => '\'Courier New\', Courier, monospace',
                    'Georgia'                  => 'Georgia, Serif',
                    'Impact'                   => 'Impact, Charcoal, sans-serif',
                    'Lucida Console'           => '\'Lucida Console\', Monaco, monospace',
                    'Lucida Sans Unicode'      => '\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif',
                    'Palatino'                 => '\'Palatino Linotype\', \'Book Antiqua\', Palatino, serif',
                    'Tahoma'                   => 'Tahoma, Geneva, sans-serif',
                    'Times New Roman'          => '\'Times New Roman\', Times, serif',
                    'Trebuchet MS'             => '\'Trebuchet MS\', Helvetica, sans-serif',
                    'Verdana'                  => 'Verdana, Geneva, sans-serif',
                ],
                'label'            => 'milex.focus.form.font',
                'label_attr'       => ['class' => 'control-label'],
                'attr'             => [
                    'class'        => 'form-control',
                    'onchange'     => 'Milex.focusUpdatePreview()',
                    'data-show-on' => '{"focus_html_mode_0":"checked"}',
                ],
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'css',
            TextareaType::class,
            [
                'label'      => 'milex.focus.form.custom.css',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'rows'         => 6,
                    'onchange'     => 'Milex.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'focus_content';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'label' => false,
            ]
        );
    }
}
