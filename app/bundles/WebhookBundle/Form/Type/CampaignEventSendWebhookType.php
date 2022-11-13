<?php

namespace Milex\WebhookBundle\Form\Type;

use Milex\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignEventRemoteUrlType.
 */
class CampaignEventSendWebhookType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ConfigType constructor.
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'url',
            UrlType::class,
            [
                'label'       => 'milex.webhook.event.sendwebhook.url',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'milex.core.value.required',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'method',
            ChoiceType::class,
            [
                'choices' => [
                    'GET'    => 'get',
                    'POST'   => 'post',
                    'PUT'    => 'put',
                    'PATCH'  => 'patch',
                    'DELETE' => 'delete',
                ],
                'multiple'   => false,
                'label_attr' => ['class' => 'control-label'],
                'label'      => 'milex.webhook.event.sendwebhook.method',
                'attr'       => [
                    'class' => 'form-control',
                ],
                'placeholder'       => false,
                'required'          => false,
                ]
        );

        $builder->add(
            'headers',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'milex.webhook.event.sendwebhook.headers',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $builder->add(
            'additional_data',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'milex.webhook.event.sendwebhook.data',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $builder->add(
            'timeout',
            NumberType::class,
            [
                'label'      => 'milex.webhook.event.sendwebhook.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'          => 'form-control',
                    'postaddon_text' => $this->translator->trans('milex.core.time.seconds'),
                ],
                'data' => !empty($options['data']['timeout']) ? $options['data']['timeout'] : 10,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'campaignevent_sendwebhook';
    }
}
