<?php

namespace Milex\PluginBundle\EventListener;

use Milex\PluginBundle\Form\Type\IntegrationsListType;
use Milex\PluginBundle\Helper\EventHelper;
use Milex\PointBundle\Event\TriggerBuilderEvent;
use Milex\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PointSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
        ];
    }

    public function onTriggerBuild(TriggerBuilderEvent $event)
    {
        $action = [
            'group'     => 'milex.plugin.point.action',
            'label'     => 'milex.plugin.actions.push_lead',
            'formType'  => IntegrationsListType::class,
            'formTheme' => 'MilexPluginBundle:FormTheme\Integration',
            'callback'  => [EventHelper::class, 'pushLead'],
        ];

        $event->addEvent('plugin.leadpush', $action);
    }
}
