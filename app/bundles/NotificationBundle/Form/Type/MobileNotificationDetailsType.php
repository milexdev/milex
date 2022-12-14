<?php

namespace Milex\NotificationBundle\Form\Type;

use Milex\CoreBundle\Form\Type\ButtonGroupType;
use Milex\CoreBundle\Form\Type\SortableListType;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MobileNotificationDetailsType extends AbstractType
{
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * MobileNotificationDetailsType constructor.
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');
        $settings    = $integration->getIntegrationSettings()->getFeatureSettings();

        $builder->add(
            'additional_data',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'milex.notification.tab.data',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        if (in_array('ios', $settings['platforms'])) {
            $builder->add(
                'ios_subtitle',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_subtitle',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.ios_subtitle.tooltip',
                    ],
                    'required' => false,
                ]
            );
            $builder->add(
                'ios_sound',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_sound',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.ios_sound.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'ios_badges',
                ButtonGroupType::class,
                [
                    'choices' => [
                        'milex.notification.form.mobile.ios_badges.set'       => 'SetTo',
                        'milex.notification.form.mobile.ios_badges.increment' => 'Increase',
                    ],
                    'attr'              => [
                        'tooltip' => 'milex.notification.form.mobile.ios_badges.tooltip',
                    ],
                    'label'       => 'milex.notification.form.mobile.ios_badges',
                    'empty_data'  => 'None',
                    'required'    => false,
                    'placeholder' => 'milex.notification.form.mobile.ios_badges.placeholder',
                    'expanded'    => true,
                    'multiple'    => false,
                ]
            );

            $builder->add(
                'ios_badgeCount',
                IntegerType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_badgecount',
                    'attr'  => [
                        'class'        => 'form-control',
                        'tooltip'      => 'milex.notification.form.mobile.ios_badgecount.tooltip',
                        'data-show-on' => '{"mobile_notification_mobileSettings_ios_badges_placeholder":""}',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'ios_contentAvailable',
                CheckboxType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_contentavailable',
                    'attr'  => [
                        'tooltip' => 'milex.notification.form.mobile.ios_contentavailable.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'ios_media',
                FileType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_media',
                    'attr'  => [
                        'tooltip' => 'milex.notification.form.mobile.ios_media.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'ios_mutableContent',
                CheckboxType::class,
                [
                    'label' => 'milex.notification.form.mobile.ios_mutablecontent',
                    'attr'  => [
                        'tooltip' => 'milex.notification.form.mobile.mutablecontent.tooltip',
                    ],
                    'required' => false,
                ]
            );
        }

        if (in_array('android', $settings['platforms'])) {
            $builder->add(
                'android_sound',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_sound',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.android_sound.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_small_icon',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_small_icon',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.android_small_icon.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_large_icon',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_large_icon',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.android_large_icon.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_big_picture',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_big_picture',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.android_big_picture.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_led_color',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_led_color',
                    'attr'  => [
                        'class'       => 'form-control',
                        'tooltip'     => 'milex.notification.form.mobile.android_led_color.tooltip',
                        'data-toggle' => 'color',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_accent_color',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_accent_color',
                    'attr'  => [
                        'class'       => 'form-control',
                        'tooltip'     => 'milex.notification.form.mobile.android_accent_color.tooltip',
                        'data-toggle' => 'color',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_group_key',
                TextType::class,
                [
                    'label' => 'milex.notification.form.mobile.android_group_key',
                    'attr'  => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.notification.form.mobile.android_group_key.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'android_lockscreen_visibility',
                ButtonGroupType::class,
                [
                    'choices' => [
                        'milex.notification.form.mobile.android_lockscreen_visibility.private' => '0',
                        'milex.notification.form.mobile.android_lockscreen_visibility.secret'  => '-1',
                    ],
                    'attr'              => [
                        'tooltip' => 'milex.notification.form.mobile.android_lockscreen_visibility.tooltip',
                    ],
                    'label'       => 'milex.notification.form.mobile.android_lockscreen_visibility',
                    'empty_data'  => '1',
                    'required'    => false,
                    'placeholder' => 'milex.notification.form.mobile.android_lockscreen_visibility.placeholder',
                    'expanded'    => true,
                    'multiple'    => false,
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'mobile_notification_details';
    }
}
