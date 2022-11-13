<?php

namespace Milex\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Class SubmitActionEmailType.
 */
class SubmitActionRepostType extends AbstractType
{
    use FormFieldTrait;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'post_url',
            UrlType::class,
            [
                'label'      => 'milex.form.action.repost.post_url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'fa fa-globe',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'milex.core.value.required',
                        ]
                    ),
                    new Url(
                        [
                            'message' => 'milex.core.valid_url_required',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'authorization_header',
            TextType::class,
            [
                'label'      => 'milex.form.action.repost.authorization_header',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'milex.form.action.repost.authorization_header.tooltip',
                    'preaddon' => 'fa fa-lock',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'failure_email',
            EmailType::class,
            [
                'label'      => 'milex.form.action.repost.failure_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'milex.form.action.repost.failure_email.tooltip',
                    'preaddon' => 'fa fa-envelope',
                ],
                'required'    => false,
                'constraints' => new Email(
                    [
                        'message' => 'milex.core.email.required',
                    ]
                ),
            ]
        );

        $fields = $this->getFormFields($options['attr']['data-formid'], false);

        foreach ($fields as $alias => $label) {
            $builder->add(
                $alias,
                TextType::class,
                [
                    'label'      => $label." ($alias)",
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'required' => false,
                ]
            );
        }
    }
}
