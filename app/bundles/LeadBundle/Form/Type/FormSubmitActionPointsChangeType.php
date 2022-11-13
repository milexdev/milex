<?php

namespace Milex\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class FormSubmitActionPointsChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'operator',
            ChoiceType::class,
            [
                'label'             => 'milex.lead.lead.submitaction.operator',
                'attr'              => ['class' => 'form-control'],
                'label_attr'        => ['class' => 'control-label'],
                'choices'           => [
                    'milex.lead.lead.submitaction.operator_plus'   => 'plus',
                    'milex.lead.lead.submitaction.operator_minus'  => 'minus',
                    'milex.lead.lead.submitaction.operator_times'  => 'times',
                    'milex.lead.lead.submitaction.operator_divide' => 'divide',
                ],
            ]
        );

        $default = (empty($options['data']['points'])) ? 0 : (int) $options['data']['points'];
        $builder->add(
            'points',
            NumberType::class,
            [
                'label'      => 'milex.lead.lead.submitaction.points',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'control-label'],
                'scale'      => 0,
                'data'       => $default,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'lead_submitaction_pointschange';
    }
}
