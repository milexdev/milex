<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Form\Type\Auth;

use Milex\IntegrationsBundle\Form\Type\NotBlankIfPublishedConstraintTrait;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait BasicAuthKeysTrait
{
    use NotBlankIfPublishedConstraintTrait;

    /**
     * @param string $usernameLabel
     * @param string $passwordLabel
     */
    private function addKeyFields(FormBuilderInterface $builder, $usernameLabel = 'milex.core.username', $passwordLabel = 'milex.core.password'): void
    {
        $builder->add(
            'username',
            TextType::class,
            [
                'label'       => $usernameLabel,
                'label_attr'  => ['class' => 'control-label'],
                'required'    => true,
                'attr'        => [
                    'class' => 'form-control',
                ],
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );

        $builder->add(
            'password',
            PasswordType::class,
            [
                'label'       => $passwordLabel,
                'label_attr'  => ['class' => 'control-label'],
                'required'    => true,
                'attr'        => [
                    'class' => 'form-control',
                ],
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );
    }
}
