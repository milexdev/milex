<?php

namespace MilexPlugin\MilexFocusBundle\EventListener;

use Milex\CampaignBundle\CampaignEvents;
use Milex\CampaignBundle\Event\CampaignBuilderEvent;
use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\PageBundle\Helper\TrackingHelper;
use MilexPlugin\MilexFocusBundle\FocusEvents;
use MilexPlugin\MilexFocusBundle\Form\Type\FocusShowType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    /**
     * @var TrackingHelper
     */
    private $trackingHelper;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(TrackingHelper $trackingHelper, RouterInterface $router)
    {
        $this->trackingHelper = $trackingHelper;
        $this->router         = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD       => ['onCampaignBuild', 0],
            FocusEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $action = [
            'label'                  => 'milex.focus.campaign.event.show_focus',
            'description'            => 'milex.focus.campaign.event.show_focus_descr',
            'eventName'              => FocusEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'formType'               => FocusShowType::class,
            'formTheme'              => 'MilexFocusBundle:FormTheme\FocusShowList',
            'formTypeOptions'        => ['update_select' => 'campaignevent_properties_focus'],
            'connectionRestrictions' => [
                'anchor' => [
                    'decision.inaction',
                ],
                'source' => [
                    'decision' => [
                        'page.pagehit',
                    ],
                ],
            ],
        ];
        $event->addAction('focus.show', $action);
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        $focusId = (int) $event->getConfig()['focus'];
        if (!$focusId) {
            return $event->setResult(false);
        }
        $values                 = [];
        $values['focus_item'][] = ['id' => $focusId, 'js' => $this->router->generate('milex_focus_generate', ['id' => $focusId], UrlGeneratorInterface::ABSOLUTE_URL)];
        $this->trackingHelper->updateSession($values);

        return $event->setResult(true);
    }
}
