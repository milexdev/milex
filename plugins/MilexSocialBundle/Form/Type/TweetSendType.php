<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TweetSendType.
 */
class TweetSendType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'channelId',
            TweetListType::class,
            [
                'label'      => 'milex.integration.Twitter.send.selecttweet',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'milex.integration.Twitter.send.selecttweet.desc',
                    'onchange' => 'Milex.disabledTweetAction()',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'milex.integration.Twitter.send.selecttweet.notblank']
                    ),
                ],
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'milex_tweet_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newTweetButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'Milex.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'fa fa-plus',
                    ],
                    'label' => 'milex.integration.Twitter.new.tweet',
                ]
            );

            // $tweet = $options['data']['channelId'];

            // create button edit tweet
            // @todo: this button requires a JS to be injected to the campaign builder
            // $windowUrlEdit = $this->router->generate(
            //     'milex_tweet_action',
            //     [
            //         'objectAction' => 'edit',
            //         'objectId'     => 'tweetId',
            //         'contentOnly'  => 1,
            //         'updateSelect' => $options['update_select'],
            //     ]
            // );

            // $builder->add(
            //     'editTweetButton',
            //     'button',
            //     [
            //         'attr' => [
            //             'class'    => 'btn btn-primary btn-nospin',
            //             'onclick'  => 'Milex.loadNewWindow(Milex.standardTweetUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
            //             'disabled' => !isset($tweet),
            //             'icon'     => 'fa fa-edit',
            //         ],
            //         'label' => 'milex.integration.Twitter.edit.tweet',
            //     ]
            // );
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
        return 'tweetsend_list';
    }
}
