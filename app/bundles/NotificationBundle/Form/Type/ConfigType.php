<?php

namespace Milex\NotificationBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ConfigType.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'notification_enabled',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.notification.config.form.notification.enabled',
                'data'  => (bool) $options['data']['notification_enabled'],
                'attr'  => [
                    'tooltip' => 'milex.notification.config.form.notification.enabled.tooltip',
                ],
            ]
        );

        $builder->add(
            'notification_landing_page_enabled',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.notification.config.form.notification.landingpage.enabled',
                'data'  => (bool) $options['data']['notification_landing_page_enabled'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.landingpage.enabled.tooltip',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'notification_tracking_page_enabled',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.notification.config.form.notification.trackingpage.enabled',
                'data'  => (bool) $options['data']['notification_tracking_page_enabled'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.trackingpage.enabled.tooltip',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'notification_app_id',
            TextType::class,
            [
                'label' => 'milex.notification.config.form.notification.app_id',
                'data'  => $options['data']['notification_app_id'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.app_id.tooltip',
                    'class'        => 'form-control',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'notification_safari_web_id',
            TextType::class,
            [
                'label' => 'milex.notification.config.form.notification.safari_web_id',
                'data'  => $options['data']['notification_safari_web_id'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.safari_web_id.tooltip',
                    'class'        => 'form-control',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'notification_rest_api_key',
            TextType::class,
            [
                'label' => 'milex.notification.config.form.notification.rest_api_key',
                'data'  => $options['data']['notification_rest_api_key'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.rest_api_key.tooltip',
                    'class'        => 'form-control',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );
        $builder->add(
            'gcm_sender_id',
            TextType::class,
            [
                'label' => 'milex.notification.config.form.notification.gcm_sender_id',
                'data'  => $options['data']['gcm_sender_id'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.gcm_sender_id.tooltip',
                    'class'        => 'form-control',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'notification_subdomain_name',
            TextType::class,
            [
                'label' => 'milex.notification.config.form.notification.subdomain_name',
                'data'  => $options['data']['notification_subdomain_name'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.subdomain_name.tooltip',
                    'class'        => 'form-control',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );

        $builder->add(
            'welcomenotification_enabled',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.notification.config.form.notification.welcome.enabled',
                'data'  => (bool) $options['data']['welcomenotification_enabled'],
                'attr'  => [
                    'tooltip'      => 'milex.notification.config.form.notification.welcome.enabled.tooltip',
                    'data-show-on' => '{"config_notificationconfig_notification_enabled_1":"checked"}',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'notificationconfig';
    }
}
