<?php

namespace Milex\PluginBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\PluginBundle\Form\Type\IntegrationsListType;
use Milex\PluginBundle\PluginEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    use PushToIntegrationTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $action = [
            'label'       => 'milex.plugin.actions.push_lead',
            'description' => 'milex.plugin.actions.tooltip',
            'formType'    => IntegrationsListType::class,
            'formTheme'   => 'MilexPluginBundle:FormTheme\Integration',
            'eventName'   => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];

        $event->addAction('plugin.leadpush', $action);
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        $config                  = $event->getConfig();
        $config['campaignEvent'] = $event->getEvent();
        $config['leadEventLog']  = $event->getLogEntry();
        $lead                    = $event->getLead();
        $errors                  = [];
        $success                 = $this->pushToIntegration($config, $lead, $errors);

        if (count($errors)) {
            $log = $event->getLogEntry();
            $log->appendToMetadata(
                [
                    'failed' => 1,
                    'reason' => implode('<br />', $errors),
                ]
            );
        }

        $event->setResult($success);
    }
}
