<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class FacebookType.
 */
class FacebookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('layout', ChoiceType::class, [
            'choices' => [
                'milex.integration.Facebook.share.layout.standard'    => 'standard',
                'milex.integration.Facebook.share.layout.buttoncount' => 'button_count',
                'milex.integration.Facebook.share.layout.button'      => 'button',
                'milex.integration.Facebook.share.layout.boxcount'    => 'box_count',
                'milex.integration.Facebook.share.layout.icon'        => 'icon',
            ],
            'label'             => 'milex.integration.Facebook.share.layout',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => ['class' => 'form-control'],
        ]);

        $builder->add('action', ChoiceType::class, [
            'choices' => [
                'milex.integration.Facebook.share.action.like'      => 'like',
                'milex.integration.Facebook.share.action.recommend' => 'recommend',
                'milex.integration.Facebook.share.action.share'     => 'share',
            ],
            'label'             => 'milex.integration.Facebook.share.action',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => ['class' => 'form-control'],
        ]);

        $builder->add('showFaces', YesNoButtonGroupType::class, [
            'label' => 'milex.integration.Facebook.share.showfaces',
            'data'  => (!isset($options['data']['showFaces'])) ? 1 : $options['data']['showFaces'],
        ]);

        $builder->add('showShare', YesNoButtonGroupType::class, [
            'label' => 'milex.integration.Facebook.share.showshare',
            'data'  => (!isset($options['data']['showShare'])) ? 1 : $options['data']['showShare'],
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'socialmedia_facebook';
    }
}
