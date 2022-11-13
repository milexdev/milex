<?php

namespace Milex\FormBundle\EventListener;

use Milex\FormBundle\Event\SubmissionEvent;
use Milex\FormBundle\Form\Type\PointActionFormSubmitType;
use Milex\FormBundle\FormEvents;
use Milex\PointBundle\Event\PointBuilderEvent;
use Milex\PointBundle\Model\PointModel;
use Milex\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PointSubscriber implements EventSubscriberInterface
{
    /**
     * @var PointModel
     */
    private $pointModel;

    public function __construct(PointModel $pointModel)
    {
        $this->pointModel = $pointModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            FormEvents::FORM_ON_SUBMIT  => ['onFormSubmit', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event)
    {
        $action = [
            'group'       => 'milex.form.point.action',
            'label'       => 'milex.form.point.action.submit',
            'description' => 'milex.form.point.action.submit_descr',
            'callback'    => ['\\Milex\\FormBundle\\Helper\\PointActionHelper', 'validateFormSubmit'],
            'formType'    => PointActionFormSubmitType::class,
        ];

        $event->addAction('form.submit', $action);
    }

    /**
     * Trigger point actions for form submit.
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $this->pointModel->triggerAction('form.submit', $event->getSubmission());
    }
}
