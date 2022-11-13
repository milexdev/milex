<?php

namespace Milex\FormBundle\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\FormBundle\Form\Type\ConfigFormType;
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
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm([
            'bundle'     => 'FormBundle',
            'formAlias'  => 'formconfig',
            'formType'   => ConfigFormType::class,
            'formTheme'  => 'MilexFormBundle:FormTheme\Config',
            'parameters' => $event->getParametersFromConfig('MilexFormBundle'),
        ]);
    }
}
