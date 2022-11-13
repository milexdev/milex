<?php

namespace Milex\EmailBundle\EventListener;

use Milex\CoreBundle\Helper\ArrayHelper;
use Milex\DashboardBundle\Entity\Widget;
use Milex\DashboardBundle\Event\WidgetDetailEvent;
use Milex\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use Milex\EmailBundle\Form\Type\DashboardEmailsInTimeWidgetType;
use Milex\EmailBundle\Form\Type\DashboardMostHitEmailRedirectsWidgetType;
use Milex\EmailBundle\Form\Type\DashboardSentEmailToContactsWidgetType;
use Milex\EmailBundle\Model\EmailModel;
use Symfony\Component\Routing\RouterInterface;

class DashboardSubscriber extends MainDashboardSubscriber
{
    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'email';

    /**
     * Define the widget(s).
     *
     * @var string
     */
    protected $types = [
        'emails.in.time' => [
            'formAlias' => DashboardEmailsInTimeWidgetType::class,
        ],
        'sent.email.to.contacts' => [
            'formAlias' => DashboardSentEmailToContactsWidgetType::class,
        ],
        'most.hit.email.redirects' => [
            'formAlias' => DashboardMostHitEmailRedirectsWidgetType::class,
        ],
        'ignored.vs.read.emails'   => [],
        'upcoming.emails'          => [],
        'most.sent.emails'         => [],
        'most.read.emails'         => [],
        'created.emails'           => [],
        'device.granularity.email' => [],
    ];

    /**
     * Define permissions to see those widgets.
     *
     * @var array
     */
    protected $permissions = [
        'email:emails:viewown',
        'email:emails:viewother',
    ];

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(EmailModel $emailModel, RouterInterface $router)
    {
        $this->emailModel = $emailModel;
        $this->router     = $router;
    }

    /**
     * Set a widget detail when needed.
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        $this->checkPermissions($event);
        $canViewOthers = $event->hasPermission('email:emails:viewother');
        $defaultLimit  = $this->getDefaultLimit($event->getWidget());

        if ('emails.in.time' == $event->getType()) {
            $widget     = $event->getWidget();
            $params     = $widget->getParams();
            $filterKeys = ['flag', 'dataset', 'companyId', 'campaignId', 'segmentId'];

            if (!$event->isCached()) {
                $event->setTemplateData([
                    'chartType'   => 'line',
                    'chartHeight' => $widget->getHeight() - 80,
                    'chartData'   => $this->emailModel->getEmailsLineChartData(
                        $params['timeUnit'],
                        $params['dateFrom'],
                        $params['dateTo'],
                        $params['dateFormat'],
                        ArrayHelper::select($filterKeys, $params),
                        $canViewOthers
                    ),
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:chart.html.php');
            $event->stopPropagation();
        }

        if ('sent.email.to.contacts' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $headItems  = [
                    'milex.dashboard.label.contact.id',
                    'milex.dashboard.label.contact.email.address',
                    'milex.dashboard.label.contact.open',
                    'milex.dashboard.label.contact.click',
                    'milex.dashboard.label.contact.links.clicked',
                    'milex.dashboard.label.email.id',
                    'milex.dashboard.label.email.name',
                    'milex.dashboard.label.segment.id',
                    'milex.dashboard.label.segment.name',
                    'milex.dashboard.label.company.id',
                    'milex.dashboard.label.company.name',
                    'milex.dashboard.label.campaign.id',
                    'milex.dashboard.label.campaign.name',
                    'milex.dashboard.label.date.sent',
                    'milex.dashboard.label.date.read',
                ];

                $event->setTemplateData(
                    [
                        'headItems' => $headItems,
                        'bodyItems' => $this->emailModel->getSentEmailToContactData(
                            ArrayHelper::getValue('limit', $params, $defaultLimit),
                            $params['dateFrom'],
                            $params['dateTo'],
                            ['groupBy' => 'sends', 'canViewOthers' => $canViewOthers],
                            ArrayHelper::getValue('companyId', $params),
                            ArrayHelper::getValue('campaignId', $params),
                            ArrayHelper::getValue('segmentId', $params)
                        ),
                    ]
                );
            }

            $event->setTemplate('MilexEmailBundle:SubscribedEvents:Dashboard/Sent.email.to.contacts.html.php');
            $event->stopPropagation();
        }

        if ('most.hit.email.redirects' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $event->setTemplateData([
                    'headItems' => [
                        'milex.dashboard.label.url',
                        'milex.dashboard.label.unique.hit.count',
                        'milex.dashboard.label.total.hit.count',
                        'milex.dashboard.label.email.id',
                        'milex.dashboard.label.email.name',
                    ],
                    'bodyItems' => $this->emailModel->getMostHitEmailRedirects(
                        ArrayHelper::getValue('limit', $params, $defaultLimit),
                        $params['dateFrom'],
                        $params['dateTo'],
                        ['groupBy' => 'sends', 'canViewOthers' => $canViewOthers],
                        ArrayHelper::getValue('companyId', $params),
                        ArrayHelper::getValue('campaignId', $params),
                        ArrayHelper::getValue('segmentId', $params)
                    ),
                ]);
            }

            $event->setTemplate('MilexEmailBundle:SubscribedEvents:Dashboard/Most.hit.email.redirects.html.php');
            $event->stopPropagation();
        }

        if ('ignored.vs.read.emails' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $event->setTemplateData([
                    'chartType'   => 'pie',
                    'chartHeight' => $widget->getHeight() - 80,
                    'chartData'   => $this->emailModel->getIgnoredVsReadPieChartData($params['dateFrom'], $params['dateTo'], [], $canViewOthers),
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:chart.html.php');
            $event->stopPropagation();
        }

        if ('upcoming.emails' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();
            $height = $widget->getHeight();
            $limit  = round(($height - 80) / 60);

            $upcomingEmails = $this->emailModel->getUpcomingEmails($limit, $canViewOthers);

            $event->setTemplate('MilexDashboardBundle:Dashboard:upcomingemails.html.php');
            $event->setTemplateData(['upcomingEmails' => $upcomingEmails]);
            $event->stopPropagation();
        }

        if ('most.sent.emails' == $event->getType()) {
            if (!$event->isCached()) {
                $params = $event->getWidget()->getParams();
                $emails = $this->emailModel->getEmailStatList(
                    ArrayHelper::getValue('limit', $params, $defaultLimit),
                    $params['dateFrom'],
                    $params['dateTo'],
                    [],
                    ['groupBy' => 'sends', 'canViewOthers' => $canViewOthers]
                );
                $items = [];

                // Build table rows with links
                if ($emails) {
                    foreach ($emails as &$email) {
                        $emailUrl = $this->router->generate('milex_email_action', ['objectAction' => 'view', 'objectId' => $email['id']]);
                        $row      = [
                            [
                                'value' => $email['name'],
                                'type'  => 'link',
                                'link'  => $emailUrl,
                            ],
                            [
                                'value' => $email['count'],
                            ],
                        ];
                        $items[] = $row;
                    }
                }

                $event->setTemplateData([
                    'headItems' => [
                        'milex.dashboard.label.title',
                        'milex.email.label.sends',
                    ],
                    'bodyItems' => $items,
                    'raw'       => $emails,
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:table.html.php');
            $event->stopPropagation();
        }

        if ('most.read.emails' == $event->getType()) {
            if (!$event->isCached()) {
                $params = $event->getWidget()->getParams();
                $emails = $this->emailModel->getEmailStatList(
                    ArrayHelper::getValue('limit', $params, $defaultLimit),
                    $params['dateFrom'],
                    $params['dateTo'],
                    [],
                    ['groupBy' => 'reads', 'canViewOthers' => $canViewOthers]
                );
                $items = [];

                // Build table rows with links
                if ($emails) {
                    foreach ($emails as &$email) {
                        $emailUrl = $this->router->generate('milex_email_action', ['objectAction' => 'view', 'objectId' => $email['id']]);
                        $row      = [
                            [
                                'value' => $email['name'],
                                'type'  => 'link',
                                'link'  => $emailUrl,
                            ],
                            [
                                'value' => $email['count'],
                            ],
                        ];
                        $items[] = $row;
                    }
                }

                $event->setTemplateData([
                    'headItems' => [
                        'milex.dashboard.label.title',
                        'milex.email.label.reads',
                    ],
                    'bodyItems' => $items,
                    'raw'       => $emails,
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:table.html.php');
            $event->stopPropagation();
        }

        if ('created.emails' == $event->getType()) {
            if (!$event->isCached()) {
                $params = $event->getWidget()->getParams();
                $emails = $this->emailModel->getEmailList(
                    ArrayHelper::getValue('limit', $params, $defaultLimit),
                    $params['dateFrom'],
                    $params['dateTo'],
                    [],
                    ['groupBy' => 'creations', 'canViewOthers' => $canViewOthers]
                );
                $items = [];

                // Build table rows with links
                if ($emails) {
                    foreach ($emails as &$email) {
                        $emailUrl = $this->router->generate(
                            'milex_email_action',
                            [
                                'objectAction' => 'view',
                                'objectId'     => $email['id'],
                            ]
                        );
                        $row = [
                            [
                                'value' => $email['name'],
                                'type'  => 'link',
                                'link'  => $emailUrl,
                            ],
                        ];
                        $items[] = $row;
                    }
                }

                $event->setTemplateData([
                    'headItems' => [
                        'milex.dashboard.label.title',
                    ],
                    'bodyItems' => $items,
                    'raw'       => $emails,
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:table.html.php');
            $event->stopPropagation();
        }
        if ('device.granularity.email' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $event->setTemplateData([
                    'chartType'   => 'pie',
                    'chartHeight' => $widget->getHeight() - 80,
                    'chartData'   => $this->emailModel->getDeviceGranularityPieChartData(
                        $params['dateFrom'],
                        $params['dateTo'],
                        $canViewOthers
                    ),
                ]);
            }

            $event->setTemplate('MilexCoreBundle:Helper:chart.html.php');
            $event->stopPropagation();
        }
    }

    /**
     * Count the row limit from the widget height.
     *
     * @return int
     */
    private function getDefaultLimit(Widget $widget)
    {
        return round((($widget->getHeight() - 80) / 35) - 1);
    }
}
