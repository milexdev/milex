<?php

namespace Milex\FormBundle\Form\Type;

use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\ThemeListType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\FormBundle\Entity\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends AbstractType
{
    /**
     * @var CorePermissions
     */
    private $security;

    public function __construct(CorePermissions $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('form.form', $options));

        //details
        $builder->add('name', TextType::class, [
            'label'      => 'milex.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add('formAttributes', TextType::class, [
            'label'      => 'milex.form.field.form.form_attr',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'milex.form.field.form.form_attr.tooltip',
            ],
            'required'   => false,
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'milex.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);

        //add category
        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'form',
            ]
        );

        $builder->add('template', ThemeListType::class, [
            'feature'     => 'form',
            'placeholder' => ' ',
            'attr'        => [
                'class'   => 'form-control',
                'tooltip' => 'milex.form.form.template.help',
            ],
        ]);

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->hasEntityAccess(
                'form:forms:publishown',
                'form:forms:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('form:forms:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'data' => $data,
            'attr' => [
                'readonly' => $readonly,
            ],
        ]);

        $builder->add('inKioskMode', YesNoButtonGroupType::class, [
            'label' => 'milex.form.form.kioskmode',
            'attr'  => [
                'tooltip' => 'milex.form.form.kioskmode.tooltip',
            ],
        ]);

        $builder->add(
            'noIndex',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.form.form.no_index',
                'data'  => $options['data']->getNoIndex() ? $options['data']->getNoIndex() : false,
            ]
        );

        $builder->add(
            'progressiveProfilingLimit',
            TextType::class,
            [
                'label' => 'milex.form.form.progressive_profiling_limit.max_fields',
                'attr'  => [
                    'style'       => 'width:75px;',
                    'class'       => 'form-control',
                    'tooltip'     => 'milex.form.form.progressive_profiling_limit.max_fields.tooltip',
                    'placeholder' => 'milex.form.form.progressive_profiling_limit_unlimited',
                ],
                'data'  => $options['data']->getProgressiveProfilingLimit() ? $options['data']->getProgressiveProfilingLimit() : '',
            ]
        );

        // Render style for new form by default
        if (null === $options['data']->getId()) {
            $options['data']->setRenderStyle(true);
        }

        $builder->add('renderStyle', YesNoButtonGroupType::class, [
            'label'      => 'milex.form.form.renderstyle',
            'data'       => (null === $options['data']->getRenderStyle()) ? true : $options['data']->getRenderStyle(),
            'attr'       => [
                'tooltip' => 'milex.form.form.renderstyle.tooltip',
            ],
        ]);

        $builder->add('publishUp', DateTimeType::class, [
            'widget'     => 'single_text',
            'label'      => 'milex.core.form.publishup',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'       => 'form-control',
                'data-toggle' => 'datetime',
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('publishDown', DateTimeType::class, [
            'widget'     => 'single_text',
            'label'      => 'milex.core.form.publishdown',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'       => 'form-control',
                'data-toggle' => 'datetime',
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('postAction', ChoiceType::class, [
            'choices' => [
                'milex.form.form.postaction.return'   => 'return',
                'milex.form.form.postaction.redirect' => 'redirect',
                'milex.form.form.postaction.message'  => 'message',
            ],
            'label'             => 'milex.form.form.postaction',
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => [
                'class'    => 'form-control',
                'onchange' => 'Milex.onPostSubmitActionChange(this.value);',
            ],
            'required'    => false,
            'placeholder' => false,
        ]);

        $postAction = (isset($options['data'])) ? $options['data']->getPostAction() : '';
        $required   = (in_array($postAction, ['redirect', 'message'])) ? true : false;
        $builder->add('postActionProperty', TextType::class, [
            'label'      => 'milex.form.form.postactionproperty',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => $required,
        ]);

        $builder->add('sessionId', HiddenType::class, [
            'mapped' => false,
        ]);

        $builder->add('buttons', FormButtonsType::class);
        $builder->add('formType', HiddenType::class, ['empty_data' => 'standalone']);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Form::class,
            'validation_groups' => [
                Form::class,
                'determineValidationGroups',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'milexform';
    }
}
