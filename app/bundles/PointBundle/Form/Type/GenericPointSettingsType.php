<?php

namespace Milex\PointBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class GenericPointSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $default = (empty($options['data']['delta'])) ? 0 : (int) $options['data']['delta'];
        $builder->add(
            'delta',
            NumberType::class,
            [
                'label'      => 'milex.point.action.delta',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.point.action.delta.help',
                    ],
                'scale'       => 0,
                'data'        => $default,
                'constraints' => [
                    new NotEqualTo(
                        [
                            'value'   => '0',
                            'message' => 'milex.core.required.value',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'genericpoint_settings';
    }
}
