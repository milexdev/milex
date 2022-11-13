<?php

namespace Milex\CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

trait ToBcBccFieldsTrait
{
    protected function addToBcBccFields(FormBuilderInterface $builder)
    {
        $builder->add(
            'to',
            TextType::class,
            [
                'label'      => 'milex.core.send.email.to',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'milex.core.optional',
                    'tooltip'     => 'milex.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new Email(
                    [
                        'message' => 'milex.core.email.required',
                    ]
                ),
            ]
        );

        $builder->add(
            'cc',
            TextType::class,
            [
                'label'      => 'milex.core.send.email.cc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'milex.core.optional',
                    'tooltip'     => 'milex.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new Email(
                    [
                        'message' => 'milex.core.email.required',
                    ]
                ),
            ]
        );

        $builder->add(
            'bcc',
            TextType::class,
            [
                'label'      => 'milex.core.send.email.bcc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'milex.core.optional',
                    'tooltip'     => 'milex.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new Email(
                    [
                        'message' => 'milex.core.email.required',
                    ]
                ),
            ]
        );
    }
}
