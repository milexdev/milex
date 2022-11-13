<?php

namespace Milex\WebhookBundle\Form\Type;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ConfigType.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('queue_mode', ChoiceType::class, [
            'choices' => [
                'milex.webhook.config.immediate_process' => 'immediate_process',
                'milex.webhook.config.cron_process'      => 'command_process',
            ],
            'label' => 'milex.webhook.config.form.queue.mode',
            'attr'  => [
                'class'   => 'form-control',
                'tooltip' => 'milex.webhook.config.form.queue.mode.tooltip',
            ],
            'placeholder' => false,
            'constraints' => [
                new NotBlank(
                    [
                        'message' => 'milex.core.value.required',
                    ]
                ),
            ],
            ]);

        $builder->add('events_orderby_dir', ChoiceType::class, [
            'choices' => [
                'milex.webhook.config.event.orderby.chronological'         => Criteria::ASC,
                'milex.webhook.config.event.orderby.reverse.chronological' => Criteria::DESC,
            ],
            'label' => 'milex.webhook.config.event.orderby',
            'attr'  => [
                'class'   => 'form-control',
                'tooltip' => 'milex.webhook.config.event.orderby.tooltip',
            ],
            'required'          => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'webhookconfig';
    }
}
