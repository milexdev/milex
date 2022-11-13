<?php

namespace Milex\WebhookBundle\Form\Type;

use Doctrine\Common\Collections\Criteria;
use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\CoreBundle\Helper\EncryptionHelper;
use Milex\WebhookBundle\Entity\Webhook;
use Milex\WebhookBundle\Form\DataTransformer\EventsToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'strict_html']));

        /** @var Webhook $webhook */
        $webhook = $builder->getData();

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'milex.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'    => 'milex.webhook.form.description',
                'required' => false,
                'attr'     => [
                    'class' => 'form-control',
                ],
            ]
        );

        $builder->add(
            'webhookUrl',
            UrlType::class,
            [
                'label'      => 'milex.webhook.form.webhook_url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'milex.webhook.form.secret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.webhook.secret.tooltip',
                ],
                'data'     => $webhook->getSecret() ?? EncryptionHelper::generateKey(),
                'required' => false,
            ]
        );

        $events = $options['events'];

        $choices = [];
        foreach ($events as $type => $event) {
            $choices[$event['label']] = $type;
        }

        $builder->add(
            'events',
            ChoiceType::class,
            [
                'choices'    => $choices,
                'multiple'   => true,
                'expanded'   => true,
                'label'      => 'milex.webhook.form.webhook.events',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => ''],
                ]
        );

        $builder->get('events')->addModelTransformer(new EventsToArrayTransformer($options['data']));

        $builder->add('buttons', FormButtonsType::class);

        $builder->add(
            'sendTest',
            ButtonType::class,
            [
                'attr'  => ['class' => 'btn btn-success', 'onclick' => 'Milex.sendHookTest(this)'],
                'label' => 'milex.webhook.send.test.payload',
            ]
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'Webhook',
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'eventsOrderbyDir',
            ChoiceType::class,
            [
                'choices' => [
                    'milex.webhook.config.event.orderby.chronological'         => Criteria::ASC,
                    'milex.webhook.config.event.orderby.reverse.chronological' => Criteria::DESC,
                ],
                'label' => 'milex.webhook.config.event.orderby',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.webhook.config.event.orderby.tooltip',
                ],
                'placeholder' => 'milex.core.form.default',
                'required'    => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Webhook::class,
            ]
        );

        $resolver->setDefined(['events']);
    }

    public function getBlockPrefix()
    {
        return 'webhook';
    }
}
