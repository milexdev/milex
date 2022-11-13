<?php

namespace Milex\CategoryBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\CustomButtonEvent;
use Milex\CoreBundle\Templating\Helper\ButtonHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ButtonSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router     = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectContactBulkButtons', 0],
        ];
    }

    public function injectContactBulkButtons(CustomButtonEvent $event)
    {
        if (0 === strpos($event->getRoute(), 'milex_contact_')) {
            $event->addButton(
                [
                    'attr' => [
                        'class'       => 'btn btn-default btn-sm btn-nospin',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MilexSharedModal',
                        'href'        => $this->router->generate('milex_category_batch_contact_view'),
                        'data-header' => $this->translator->trans('milex.lead.batch.categories'),
                    ],
                    'btnText'   => $this->translator->trans('milex.lead.batch.categories'),
                    'iconClass' => 'fa fa-cogs',
                ],
                ButtonHelper::LOCATION_BULK_ACTIONS
            );
        }
    }
}
