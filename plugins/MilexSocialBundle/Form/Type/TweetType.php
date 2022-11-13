<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Milex\AssetBundle\Form\Type\AssetListType;
use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\PageBundle\Form\Type\PageListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TweetType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'milex.social.monitoring.twitter.tweet.name',
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'milex.social.monitoring.twitter.tweet.name.tooltip',
                    'class'   => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'milex.core.name.required',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'      => 'milex.social.monitoring.twitter.tweet.description',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'milex.social.monitoring.twitter.tweet.description.tooltip',
                    'class'   => 'form-control',
                ],
            ]
        );

        $builder->add(
            'text',
            TextareaType::class,
            [
                'label'      => 'milex.social.monitoring.twitter.tweet.text',
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'milex.social.monitoring.twitter.tweet.text.tooltip',
                    'class'   => 'form-control tweet-message',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'milex.core.value.required',
                        ]
                    ),
                ],
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MilexAssetBundle:Asset', 'id');
        $builder->add(
                $builder->create(
                'asset',
                AssetListType::class,
                [
                    'label'       => 'milex.social.monitoring.twitter.assets',
                    'placeholder' => 'milex.social.monitoring.list.choose',
                    'label_attr'  => ['class' => 'control-label'],
                    'multiple'    => false,
                    'attr'        => [
                        'class'   => 'form-control tweet-insert-asset',
                        'tooltip' => 'milex.social.monitoring.twitter.assets.descr',
                    ],
                ]
            )->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MilexPageBundle:Page', 'id');
        $builder->add(
            $builder->create(
                'page',
                PageListType::class,
                [
                    'label'       => 'milex.social.monitoring.twitter.pages',
                    'placeholder' => 'milex.social.monitoring.list.choose',
                    'label_attr'  => ['class' => 'control-label'],
                    'multiple'    => false,
                    'attr'        => [
                        'class'   => 'form-control tweet-insert-page',
                        'tooltip' => 'milex.social.monitoring.twitter.pages.descr',
                    ],
                ]
            )->addModelTransformer($transformer)
        );

        $builder->add(
            'handle',
            ButtonType::class,
            [
                'label' => 'milex.social.twitter.handle',
                'attr'  => [
                    'class' => 'form-control btn-primary tweet-insert-handle',
                ],
            ]
        );

        //add category
        $builder->add('category', CategoryListType::class, [
            'bundle' => 'plugin:milexSocial',
        ]);

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text' => false,
                ]
            );
            $builder->add(
                'updateSelect',
                HiddenType::class,
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class
            );
        }

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select']);
    }

    public function getBlockPrefix()
    {
        return 'twitter_tweet';
    }
}
