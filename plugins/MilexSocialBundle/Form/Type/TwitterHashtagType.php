<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TwitterHashtagType extends TwitterAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hashtag', TextType::class, [
            'label'      => 'milex.social.monitoring.twitter.hashtag',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'tooltip'  => 'milex.social.monitoring.twitter.hashtag.tooltip',
                'class'    => 'form-control',
                'preaddon' => 'symbol-hashtag',
            ],
        ]);

        $builder->add('checknames', ChoiceType::class, [
            'choices' => [
                'milex.social.monitoring.twitter.no'  => '0',
                'milex.social.monitoring.twitter.yes' => '1',
            ],
            'label'             => 'milex.social.monitoring.twitter.namematching',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => [
                'class'   => 'form-control',
                'tooltip' => 'milex.social.monitoring.twitter.namematching.tooltip',
            ],
        ]);

        // pull in the parent type's form builder
        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix()
    {
        return 'twitter_hashtag';
    }
}
