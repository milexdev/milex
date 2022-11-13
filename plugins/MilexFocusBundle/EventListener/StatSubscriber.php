<?php

namespace MilexPlugin\MilexFocusBundle\EventListener;

use Milex\FormBundle\Event\SubmissionEvent;
use Milex\FormBundle\FormEvents;
use Milex\PageBundle\Event\PageHitEvent;
use Milex\PageBundle\PageEvents;
use MilexPlugin\MilexFocusBundle\Entity\Stat;
use MilexPlugin\MilexFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StatSubscriber implements EventSubscriberInterface
{
    /**
     * @var FocusModel
     */
    private $model;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(FocusModel $model, RequestStack $requestStack)
    {
        $this->model        = $model;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PageEvents::PAGE_ON_HIT    => ['onPageHit', 0],
            FormEvents::FORM_ON_SUBMIT => ['onFormSubmit', 0],
        ];
    }

    public function onPageHit(PageHitEvent $event)
    {
        $hit    = $event->getHit();
        $source = $hit->getSource();

        if ('focus' == $source || 'focus.focus' == $source) {
            $sourceId = $hit->getSourceId();
            $focus    = $this->model->getEntity($sourceId);

            if ($focus && $focus->isPublished()) {
                $this->model->addStat($focus, Stat::TYPE_CLICK, $hit, $hit->getLead());
            }
        }
    }

    /**
     * Note if this submission is from a focus submit.
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        // Check the request for a focus field
        $milexform = $this->requestStack->getCurrentRequest()->request->get('milexform', []);
        $id         = $milexform['focusId'] ?? false;

        if (!empty($id)) {
            $focus = $this->model->getEntity($id);

            if ($focus && $focus->isPublished()) {
                // Make sure the form is still applicable
                $form = $event->getSubmission()->getForm();
                if ((int) $form->getId() === (int) $focus->getForm()) {
                    $this->model->addStat($focus, Stat::TYPE_FORM, $event->getSubmission(), $event->getLead());
                }
            }
        }
    }
}
