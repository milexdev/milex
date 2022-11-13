<?php

namespace Milex\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SlotSocialFollowType extends SlotType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'flink',
            TextType::class,
            [
                'label'      => 'milex.core.facebook.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'value'           => 'http://www.facebook.com',
                    'class'           => 'form-control',
                    'data-slot-param' => 'flink',
                ],
            ]
        );

        $builder->add(
            'tlink',
            TextType::class,
            [
                'label'      => 'milex.core.twitter.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'value'           => 'http://www.twitter.com',
                    'class'           => 'form-control',
                    'data-slot-param' => 'tlink',
                ],
            ]
        );

        $builder->add(
            'align',
            ButtonGroupType::class,
            [
                'label'             => 'milex.core.image.position',
                'label_attr'        => ['class' => 'control-label'],
                'required'          => false,
                'attr'              => [
                    'class'           => 'form-control',
                    'data-slot-param' => 'align',
                ],
                'choices'           => [
                    'milex.core.left'   => 0,
                    'milex.core.center' => 1,
                    'milex.core.right'  => 2,
                ],
                ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'slot_socialfollow';
    }
}
