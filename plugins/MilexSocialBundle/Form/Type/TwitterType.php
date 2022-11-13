<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TwitterType.
 */
class TwitterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('count', ChoiceType::class, [
            'choices' => [
                'milex.integration.Twitter.share.layout.horizontal' => 'horizontal',
                'milex.integration.Twitter.share.layout.vertical'   => 'vertical',
                'milex.integration.Twitter.share.layout.none'       => 'none',
            ],
            'label'             => 'milex.integration.Twitter.share.layout',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => ['class' => 'form-control'],
        ]);

        $builder->add('text', TextType::class, [
            'label_attr' => ['class' => 'control-label'],
            'label'      => 'milex.integration.Twitter.share.text',
            'required'   => false,
            'attr'       => [
                'class'       => 'form-control',
                'placeholder' => 'milex.integration.Twitter.share.text.pagetitle',
            ],
        ]);

        $builder->add('via', TextType::class, [
            'label_attr' => ['class' => 'control-label'],
            'label'      => 'milex.integration.Twitter.share.via',
            'required'   => false,
            'attr'       => [
                'class'       => 'form-control',
                'placeholder' => 'milex.integration.Twitter.share.username',
                'preaddon'    => 'fa fa-at',
            ],
        ]);

        $builder->add('related', TextType::class, [
            'label_attr' => ['class' => 'control-label'],
            'label'      => 'milex.integration.Twitter.share.related',
            'required'   => false,
            'attr'       => [
                'class'       => 'form-control',
                'placeholder' => 'milex.integration.Twitter.share.username',
                'preaddon'    => 'fa fa-at',
            ],
        ]);

        $builder->add('hashtags', TextType::class, [
            'label_attr' => ['class' => 'control-label'],
            'label'      => 'milex.integration.Twitter.share.hashtag',
            'required'   => false,
            'attr'       => [
                'class'       => 'form-control',
                'placeholder' => 'milex.integration.Twitter.share.hashtag.placeholder',
                'preaddon'    => 'symbol-hashtag',
            ],
        ]);

        $builder->add('size', YesNoButtonGroupType::class, [
            'no_value'  => 'medium',
            'yes_value' => 'large',
            'label'     => 'milex.integration.Twitter.share.largesize',
            'data'      => (!empty($options['data']['size'])) ? $options['data']['size'] : 'medium',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'socialmedia_twitter';
    }
}
