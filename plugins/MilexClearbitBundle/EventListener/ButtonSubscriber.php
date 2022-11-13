<?php

namespace MilexPlugin\MilexClearbitBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\CustomButtonEvent;
use Milex\CoreBundle\Templating\Helper\ButtonHelper;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexClearbitBundle\Integration\ClearbitIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ButtonSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $helper;

    /**
     * @var RouterInterface
     */
    private $translator;

    /**
     * @var TranslatorInterface
     */
    private $router;

    public function __construct(IntegrationHelper $helper, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->helper     = $helper;
        $this->translator = $translator;
        $this->router     = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectViewButtons', 0],
        ];
    }

    public function injectViewButtons(CustomButtonEvent $event)
    {
        /** @var ClearbitIntegration $myIntegration */
        $myIntegration = $this->helper->getIntegrationObject('Clearbit');

        if (false === $myIntegration || !$myIntegration->getIntegrationSettings()->getIsPublished()) {
            return;
        }

        if (0 === strpos($event->getRoute(), 'milex_contact_')) {
            $event->addButton(
                [
                    'attr' => [
                        'class'       => 'btn btn-default btn-sm btn-nospin',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MilexSharedModal',
                        'onclick'     => 'this.href=\''.
                            $this->router->generate(
                                'milex_plugin_clearbit_action',
                                ['objectAction' => 'batchLookupPerson']
                            ).
                            '?\' + mQuery.param({\'clearbit_batch_lookup\':{\'ids\':JSON.parse(Milex.getCheckedListIds(false, true))}});return true;',
                        'data-header' => $this->translator->trans('milex.plugin.clearbit.button.caption'),
                    ],
                    'btnText'   => $this->translator->trans('milex.plugin.clearbit.button.caption'),
                    'iconClass' => 'fa fa-search',
                ],
                ButtonHelper::LOCATION_BULK_ACTIONS
            );

            if ($event->getItem()) {
                $lookupContactButton = [
                    'attr' => [
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MilexSharedModal',
                        'data-header' => $this->translator->trans(
                            'milex.plugin.clearbit.lookup.header',
                            ['%item%' => $event->getItem()->getEmail()]
                        ),
                        'href' => $this->router->generate(
                            'milex_plugin_clearbit_action',
                            ['objectId' => $event->getItem()->getId(), 'objectAction' => 'lookupPerson']
                        ),
                    ],
                    'btnText'   => $this->translator->trans('milex.plugin.clearbit.button.caption'),
                    'iconClass' => 'fa fa-search',
                ];

                $event->addButton(
                    $lookupContactButton,
                    ButtonHelper::LOCATION_PAGE_ACTIONS,
                    ['milex_contact_action', ['objectAction' => 'view']]
                );

                $event->addButton(
                    $lookupContactButton,
                    ButtonHelper::LOCATION_LIST_ACTIONS,
                    'milex_contact_index'
                );
            }
        } else {
            if (0 === strpos($event->getRoute(), 'milex_company_')) {
                $event->addButton(
                    [
                        'attr' => [
                            'class'       => 'btn btn-default btn-sm btn-nospin',
                            'data-toggle' => 'ajaxmodal',
                            'data-target' => '#MilexSharedModal',
                            'onclick'     => 'this.href=\''.
                                $this->router->generate(
                                    'milex_plugin_clearbit_action',
                                    ['objectAction' => 'batchLookupCompany']
                                ).
                                '?\' + mQuery.param({\'clearbit_batch_lookup\':{\'ids\':JSON.parse(Milex.getCheckedListIds(false, true))}});return true;',
                            'data-header' => $this->translator->trans(
                                'milex.plugin.clearbit.button.caption'
                            ),
                        ],
                        'btnText'   => $this->translator->trans('milex.plugin.clearbit.button.caption'),
                        'iconClass' => 'fa fa-search',
                    ],
                    ButtonHelper::LOCATION_BULK_ACTIONS
                );

                if ($event->getItem()) {
                    $lookupCompanyButton = [
                        'attr' => [
                            'data-toggle' => 'ajaxmodal',
                            'data-target' => '#MilexSharedModal',
                            'data-header' => $this->translator->trans(
                                'milex.plugin.clearbit.lookup.header',
                                ['%item%' => $event->getItem()->getName()]
                            ),
                            'href' => $this->router->generate(
                                'milex_plugin_clearbit_action',
                                ['objectId' => $event->getItem()->getId(), 'objectAction' => 'lookupCompany']
                            ),
                        ],
                        'btnText'   => $this->translator->trans('milex.plugin.clearbit.button.caption'),
                        'iconClass' => 'fa fa-search',
                    ];

                    $event->addButton(
                        $lookupCompanyButton,
                        ButtonHelper::LOCATION_LIST_ACTIONS,
                        'milex_company_index'
                    );
                }
            }
        }
    }
}
