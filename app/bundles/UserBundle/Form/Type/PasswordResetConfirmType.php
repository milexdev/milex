<?php

namespace Milex\UserBundle\Form\Type;

use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetConfirmType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber([]));

        $builder->add(
            'identifier',
            TextType::class,
            [
                'label'      => 'milex.user.auth.form.loginusername',
                'label_attr' => ['class' => 'sr-only'],
                'attr'       => [
                    'class'       => 'form-control',
                    'preaddon'    => 'fa fa-user',
                    'placeholder' => 'milex.user.auth.form.loginusername',
                ],
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'milex.user.user.passwordreset.notblank']),
                ],
            ]
        );

        $builder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'first_name'    => 'password',
                'first_options' => [
                    'label'      => 'milex.core.password',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'placeholder'  => 'milex.user.user.passwordreset.password.placeholder',
                        'tooltip'      => 'milex.user.user.form.help.passwordrequirements',
                        'preaddon'     => 'fa fa-lock',
                        'autocomplete' => 'off',
                    ],
                    'required'       => true,
                    'error_bubbling' => false,
                    'constraints'    => [
                        new Assert\NotBlank(['message' => 'milex.user.user.passwordreset.notblank']),
                        new Assert\Length([
                            'min'        => 6,
                            'minMessage' => 'milex.user.user.password.minlength',
                        ]),
                    ],
                ],
                'second_name'    => 'confirm',
                'second_options' => [
                    'label'      => 'milex.user.user.form.passwordconfirm',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'placeholder'  => 'milex.user.user.passwordreset.confirm.placeholder',
                        'tooltip'      => 'milex.user.user.form.help.passwordrequirements',
                        'preaddon'     => 'fa fa-lock',
                        'autocomplete' => 'off',
                    ],
                    'required'       => true,
                    'error_bubbling' => false,
                    'constraints'    => [
                        new Assert\NotBlank(['message' => 'milex.user.user.passwordreset.notblank']),
                    ],
                ],
                'type'            => PasswordType::class,
                'invalid_message' => 'milex.user.user.password.mismatch',
                'required'        => true,
                'error_bubbling'  => false,
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'attr' => [
                    'class' => 'btn btn-lg btn-primary btn-block',
                ],
                'label' => 'milex.user.user.passwordreset.reset',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'passwordresetconfirm';
    }
}
