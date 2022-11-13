<?php

namespace Milex\LeadBundle\Form\Type;

use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\CoreBundle\Form\Validator\Constraints\CircularDependency;
use Milex\LeadBundle\Entity\LeadList;
use Milex\LeadBundle\Form\DataTransformer\FieldFilterTransformer;
use Milex\LeadBundle\Model\ListModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ListType extends AbstractType
{
    private $translator;

    /**
     * @var ListModel
     */
    private $listModel;

    public function __construct(TranslatorInterface $translator, ListModel $listModel)
    {
        $this->translator = $translator;
        $this->listModel  = $listModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('lead.list', $options));

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'milex.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'publicName',
            TextType::class,
            [
                'label'      => 'milex.lead.list.form.publicname',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.lead.list.form.publicname.tooltip',
                ],
                'required' => false,
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
                    'length'  => 25,
                    'tooltip' => 'milex.lead.list.help.alias',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'      => 'milex.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor'],
                'required'   => false,
            ]
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'segment',
            ]
        );

        $builder->add(
            'isGlobal',
            YesNoButtonGroupType::class,
            [
                'label'      => 'milex.lead.list.form.isglobal',
                'attr'       => [
                    'tooltip' => 'milex.lead.list.form.isglobal.tooltip',
                ],
            ]
        );

        $builder->add(
            'isPreferenceCenter',
            YesNoButtonGroupType::class,
            [
                'label'      => 'milex.lead.list.form.isPreferenceCenter',
                'attr'       => [
                    'tooltip' => 'milex.lead.list.form.isPreferenceCenter.tooltip',
                ],
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $filterModalTransformer = new FieldFilterTransformer($this->translator, ['object' => 'lead']);
        $builder->add(
            $builder->create(
                'filters',
                CollectionType::class,
                [
                    'entry_type'     => FilterType::class,
                    'error_bubbling' => false,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'label'          => false,
                    'constraints'    => [
                        new CircularDependency([
                            'message' => 'milex.core.segment.circular_dependency_exists',
                        ]),
                    ],
                ]
            )->addModelTransformer($filterModalTransformer)
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
                'data_class' => LeadList::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields'] = $this->listModel->getChoiceFields();
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'leadlist';
    }
}
