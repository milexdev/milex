<?php

namespace Milex\PageBundle\Form\Type;

use Milex\PageBundle\Model\PageModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class VariantType.
 */
class VariantType extends AbstractType
{
    /**
     * @var PageModel
     */
    private $pageModel;

    public function __construct(PageModel $pageModel)
    {
        $this->pageModel = $pageModel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'weight',
            IntegerType::class, [
                'label'      => 'milex.core.ab_test.form.traffic_weight',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.core.ab_test.form.traffic_weight.help',
                ],
                'constraints' => [
                    new NotBlank(
                        ['message' => 'milex.page.variant.weight.notblank']
                    ),
                ],
            ]
        );

        $abTestWinnerCriteria = $this->pageModel->getBuilderComponents(null, 'abTestWinnerCriteria');

        if (!empty($abTestWinnerCriteria)) {
            $criteria = $abTestWinnerCriteria['criteria'];
            $choices  = $abTestWinnerCriteria['choices'];

            $builder->add(
                'winnerCriteria',
                ChoiceType::class, [
                    'label'      => 'milex.core.ab_test.form.winner',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => 'Milex.getAbTestWinnerForm(\'page\', \'page\', this);',
                    ],
                    'expanded'    => false,
                    'multiple'    => false,
                    'choices'     => $choices,
                    'placeholder' => 'milex.core.form.chooseone',
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'milex.core.ab_test.winner_criteria.not_blank']
                        ),
                    ],
                    ]
            );

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($criteria) {
                $form = $event->getForm();
                $data = $event->getData();
                if (isset($data['winnerCriteria'])) {
                    if (!empty($criteria[$data['winnerCriteria']]['formType'])) {
                        $formTypeOptions = [
                            'required' => false,
                            'label'    => false,
                        ];
                        if (!empty($criteria[$data]['formTypeOptions'])) {
                            $formTypeOptions = array_merge($formTypeOptions, $criteria[$data]['formTypeOptions']);
                        }
                        $form->add('properties', $criteria[$data]['formType'], $formTypeOptions);
                    }
                }
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pagevariant';
    }
}
