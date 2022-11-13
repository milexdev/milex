<?php

namespace MilexPlugin\MilexFocusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class FocusShowType.
 */
class FocusShowType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'focus',
            FocusListType::class,
            [
                'label'      => 'milex.focus.focusitem.selectitem',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'milex.focus.focusitem.selectitem_descr',
                    'onchange' => 'Milex.disabledFocusActions()',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'milex.focus.choosefocus.notblank']
                    ),
                ],
                'data' => isset($options['data']['focus']) ? $options['data']['focus'] : null,
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'milex_focus_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newFocusButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'Milex.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'fa fa-plus',
                    ],
                    'label' => 'milex.focus.show.new.item',
                ]
            );

            // create button edit focus
            $windowUrlEdit = $this->router->generate(
                'milex_focus_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'focusId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editFocusButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'Milex.loadNewWindow(Milex.standardFocusUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
                        'disabled' => !isset($options['data']['focus']),
                        'icon'     => 'fa fa-edit',
                    ],
                    'label' => 'milex.focus.show.edit.item',
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'focusshow_list';
    }
}
