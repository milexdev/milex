<?php

namespace Milex\CategoryBundle\Form\Type;

use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber([]));
        $builder->addEventSubscriber(new FormExitSubscriber('category.category', $options));

        if (!$options['data']->getId()) {
            // Do not allow custom bundle
            if (true == $options['show_bundle_select']) {
                // Create new category from category bundle - let user select the bundle
                $selected = $this->session->get('milex.category.type', 'category');
                $builder->add(
                    'bundle',
                    CategoryBundlesType::class,
                    [
                        'label'      => 'milex.core.type',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => ['class' => 'form-control'],
                        'required'   => true,
                        'data'       => $selected,
                    ]
                );
            } else {
                // Create new category directly from another bundle - preset bundle
                $builder->add(
                    'bundle',
                    HiddenType::class,
                    [
                        'data' => $options['bundle'],
                    ]
                );
            }
        }

        $builder->add(
            'title',
            TextType::class,
            [
                'label'      => 'milex.core.title',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label'      => 'milex.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'alias',
            TextType::class,
            [
                'label'      => 'milex.core.alias',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.category.form.alias.help',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'color',
            TextType::class,
            [
                'label'      => 'milex.core.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required' => false,
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'inForm',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Milex\CategoryBundle\Entity\Category',
                'show_bundle_select' => false,
                'bundle'             => function (Options $options) {
                    if (!$bundle = $options['data']->getBundle()) {
                        $bundle = 'category';
                    }

                    return $bundle;
                },
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'category_form';
    }
}
