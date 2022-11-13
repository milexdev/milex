<?php

namespace MilexPlugin\MilexSocialBundle\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\ConfigBundle\Event\ConfigEvent;
use MilexPlugin\MilexSocialBundle\Form\Type\ConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
            ConfigEvents::CONFIG_PRE_SAVE    => ['onConfigSave', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm(
            [
                'formAlias'  => 'social_config',
                'formTheme'  => 'MilexSocialBundle:FormTheme\Config',
                'formType'   => ConfigType::class,
                'parameters' => $event->getParametersFromConfig('MilexSocialBundle'),
            ]
        );
    }

    public function onConfigSave(ConfigEvent $event)
    {
        /** @var array $values */
        $values = $event->getConfig();

        // Manipulate the values
        if (!empty($values['social_config']['twitter_handle_field'])) {
            $values['social_config']['twitter_handle_field'] = htmlspecialchars($values['social_config']['twitter_handle_field']);
        }

        // Set updated values
        $event->setConfig($values);
    }
}
