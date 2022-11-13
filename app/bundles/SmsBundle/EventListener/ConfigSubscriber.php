<?php

namespace Milex\SmsBundle\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\SmsBundle\Form\Type\ConfigType;
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
            'bundle'     => 'SmsBundle',
            'formAlias'  => 'smsconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => 'MilexSmsBundle:FormTheme\Config',
            'parameters' => $event->getParametersFromConfig('MilexSmsBundle'),
        ]);
    }
}
