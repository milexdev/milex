<?php

namespace Milex\PageBundle\EventListener;

use Milex\PageBundle\Event as Events;
use Milex\PageBundle\Form\Type\PointActionPageHitType;
use Milex\PageBundle\Form\Type\PointActionUrlHitType;
use Milex\PageBundle\Helper\PointActionHelper;
use Milex\PageBundle\PageEvents;
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
            PageEvents::PAGE_ON_HIT     => ['onPageHit', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event)
    {
        $action = [
            'group'       => 'milex.page.point.action',
            'label'       => 'milex.page.point.action.pagehit',
            'description' => 'milex.page.point.action.pagehit_descr',
            'callback'    => [PointActionHelper::class, 'validatePageHit'],
            'formType'    => PointActionPageHitType::class,
        ];

        $event->addAction('page.hit', $action);

        $action = [
            'group'       => 'milex.page.point.action',
            'label'       => 'milex.page.point.action.urlhit',
            'description' => 'milex.page.point.action.urlhit_descr',
            'callback'    => [PointActionHelper::class, 'validateUrlHit'],
            'formType'    => PointActionUrlHitType::class,
            'formTheme'   => 'MilexPageBundle:FormTheme\Point',
        ];

        $event->addAction('url.hit', $action);
    }

    /**
     * Trigger point actions for page hits.
     */
    public function onPageHit(Events\PageHitEvent $event)
    {
        if ($event->getPage()) {
            // Milex Landing Page was hit
            $this->pointModel->triggerAction('page.hit', $event->getHit());
        } else {
            // Milex Tracking Pixel was hit
            $this->pointModel->triggerAction('url.hit', $event->getHit());
        }
    }
}
