<?php

namespace Milex\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SlotType.
 */
class SlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'padding-top',
            NumberType::class,
            [
                'label'      => 'milex.core.padding.top',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'           => 'form-control',
                    'data-slot-param' => 'padding-top',
                    'postaddon_text'  => 'px',
                ],
            ]
        );

        $builder->add(
            'padding-bottom',
            NumberType::class,
            [
                'label'      => 'milex.core.padding.bottom',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'           => 'form-control',
                    'data-slot-param' => 'padding-bottom',
                    'postaddon_text'  => 'px',
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'slot';
    }
}
