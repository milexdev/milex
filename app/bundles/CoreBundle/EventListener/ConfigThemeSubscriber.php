<?php

namespace Milex\CoreBundle\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\CoreBundle\Form\Type\ConfigThemeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigThemeSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm(
            [
                'bundle'     => 'CoreBundle',
                'formAlias'  => 'themeconfig',
                'formType'   => ConfigThemeType::class,
                'formTheme'  => 'MilexCoreBundle:FormTheme\Config',
                'parameters' => [
                    'theme'                           => $event->getParametersFromConfig('MilexCoreBundle')['theme'],
                    'theme_import_allowed_extensions' => $event->getParametersFromConfig('MilexCoreBundle')['theme_import_allowed_extensions'],
                ],
            ]
        );
    }
}
