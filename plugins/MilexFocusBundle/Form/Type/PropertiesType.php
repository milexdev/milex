<?php

namespace MilexPlugin\MilexFocusBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'bar',
            FocusPropertiesType::class,
            [
                'focus_style' => 'bar',
                'data'        => (isset($options['data']['bar'])) ? $options['data']['bar'] : [],
            ]
        );

        $builder->add(
            'modal',
            FocusPropertiesType::class,
            [
                'focus_style' => 'modal',
                'data'        => (isset($options['data']['modal'])) ? $options['data']['modal'] : [],
            ]
        );

        $builder->add(
            'notification',
            FocusPropertiesType::class,
            [
                'focus_style' => 'notification',
                'data'        => (isset($options['data']['notification'])) ? $options['data']['notification'] : [],
            ]
        );

        $builder->add(
            'page',
            FocusPropertiesType::class,
            [
                'focus_style' => 'page',
                'data'        => (isset($options['data']['page'])) ? $options['data']['page'] : [],
            ]
        );

        $builder->add(
            'animate',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.focus.form.animate',
                'data'  => (isset($options['data']['animate'])) ? $options['data']['animate'] : true,
                'attr'  => [
                    'onchange' => 'Milex.focusUpdatePreview()',
                ],
            ]
        );

        $builder->add(
            'link_activation',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.focus.form.activate_for_links',
                'data'  => (isset($options['data']['link_activation'])) ? $options['data']['link_activation'] : true,
                'attr'  => [
                    'data-show-on' => '{"focus_properties_when": ["leave"]}',
                ],
            ]
        );

        $builder->add(
            'colors',
            ColorType::class,
            [
                'label' => false,
            ]
        );

        $builder->add(
            'content',
            ContentType::class,
            [
                'label' => false,
            ]
        );

        $builder->add(
            'when',
            ChoiceType::class,
            [
                'choices'           => [
                    'milex.focus.form.when.immediately'   => 'immediately',
                    'milex.focus.form.when.scroll_slight' => 'scroll_slight',
                    'milex.focus.form.when.scroll_middle' => 'scroll_middle',
                    'milex.focus.form.when.scroll_bottom' => 'scroll_bottom',
                    'milex.focus.form.when.leave'         => 'leave',
                ],
                'label'       => 'milex.focus.form.when',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'expanded'    => false,
                'multiple'    => false,
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'timeout',
            TextType::class,
            [
                'label'      => 'milex.focus.form.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'          => 'form-control',
                    'postaddon_text' => 'sec',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'frequency',
            ChoiceType::class,
            [
                'choices'           => [
                    'milex.focus.form.frequency.everypage' => 'everypage',
                    'milex.focus.form.frequency.once'      => 'once',
                    'milex.focus.form.frequency.q2m'       => 'q2min',
                    'milex.focus.form.frequency.q15m'      => 'q15min',
                    'milex.focus.form.frequency.hourly'    => 'hourly',
                    'milex.focus.form.frequency.daily'     => 'daily',
                ],
                'label'       => 'milex.focus.form.frequency',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'expanded'    => false,
                'multiple'    => false,
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'stop_after_conversion',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.focus.form.engage_after_conversion',
                'data'  => (isset($options['data']['stop_after_conversion'])) ? $options['data']['stop_after_conversion'] : true,
                'attr'  => [
                    'tooltip' => 'milex.focus.form.engage_after_conversion.tooltip',
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'focus_entity_properties';
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
