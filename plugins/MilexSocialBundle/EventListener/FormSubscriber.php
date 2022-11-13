<?php

namespace MilexPlugin\MilexSocialBundle\EventListener;

use Milex\FormBundle\Event\FormBuilderEvent;
use Milex\FormBundle\FormEvents;
use MilexPlugin\MilexSocialBundle\Form\Type\SocialLoginType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD => ['onFormBuild', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event)
    {
        $action = [
            'label'          => 'milex.plugin.actions.socialLogin',
            'formType'       => SocialLoginType::class,
            'template'       => 'MilexSocialBundle:Integration:login.html.php',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => false,
                'addDefaultValue'  => false,
                'addSaveResult'    => false,
            ],
        ];

        $event->addFormField('plugin.loginSocial', $action);
    }
}
