<?php

namespace Milex\PluginBundle\EventListener;

use Milex\FormBundle\Event\FormBuilderEvent;
use Milex\FormBundle\Event\SubmissionEvent;
use Milex\FormBundle\FormEvents;
use Milex\PluginBundle\Form\Type\IntegrationsListType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormSubscriber implements EventSubscriberInterface
{
    use PushToIntegrationTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD            => ['onFormBuild', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION => ['onFormSubmitActionTriggered', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event)
    {
        $event->addSubmitAction('plugin.leadpush', [
            'group'       => 'milex.plugin.actions',
            'description' => 'milex.plugin.actions.tooltip',
            'label'       => 'milex.plugin.actions.push_lead',
            'formType'    => IntegrationsListType::class,
            'formTheme'   => 'MilexPluginBundle:FormTheme\Integration',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);
    }

    /**
     * @return mixed
     */
    public function onFormSubmitActionTriggered(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('plugin.leadpush')) {
            return;
        }

        $this->pushToIntegration($event->getActionConfig(), $event->getSubmission()->getLead());
    }
}
