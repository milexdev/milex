<?php

namespace Milex\ChannelBundle\Form\Type;

use Milex\ChannelBundle\Model\MessageModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class MessageSendType.
 */
class MessageSendType extends AbstractType
{
    protected $router;
    protected $messageModel;

    /**
     * MessageSendType constructor.
     */
    public function __construct(RouterInterface $router, MessageModel $messageModel)
    {
        $this->router       = $router;
        $this->messageModel = $messageModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'marketingMessage',
            MessageListType::class,
            [
                'label'       => 'milex.channel.send.selectmessages',
                'label_attr'  => ['class' => 'control-label'],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'milex.channel.choosemessage.notblank']
                    ),
                ],
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'milex_message_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newMarketingMessageButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'Milex.loadNewWindow({windowUrl: \''.$windowUrl.'\'})',
                        'icon'    => 'fa fa-plus',
                    ],
                    'label' => 'milex.channel.create.new.message',
                ]
            );

            // create button edit email
            $windowUrlEdit = $this->router->generate(
                'milex_message_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'messageId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editMessageButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'Milex.loadNewWindow({windowUrl: \''.$windowUrlEdit.'\'})',
                        'disabled' => !isset($options['data']['message']),
                        'icon'     => 'fa fa-edit',
                    ],
                    'label' => 'milex.channel.send.edit.message',
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'message_send';
    }
}
