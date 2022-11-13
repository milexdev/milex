<?php

namespace Milex\CampaignBundle\EventListener;

use Milex\CoreBundle\Helper\Chart\ChartQuery;
use Milex\LeadBundle\Model\CompanyReportData;
use Milex\ReportBundle\Event\ReportBuilderEvent;
use Milex\ReportBundle\Event\ReportGeneratorEvent;
use Milex\ReportBundle\Event\ReportGraphEvent;
use Milex\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_CAMPAIGN_LEAD_EVENT_LOG = 'campaign_lead_event_log';

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    public function __construct(CompanyReportData $companyReportData)
    {
        $this->companyReportData = $companyReportData;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD          => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE       => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_GRAPH_GENERATE => ['onReportGraphGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext(self::CONTEXT_CAMPAIGN_LEAD_EVENT_LOG)) {
            return;
        }

        $prefix           = 'log.';
        $aliasPrefix      = 'log_';
        $campaignPrefix   = 'c.';
        $eventPrefix      = 'e.';
        $eventAliasPrefix = 'e_';
        $catPrefix        = 'cat.';
        $leadPrefix       = 'l.';

        $columns = [
            // Log columns
            $prefix.'date_triggered' => [
                'label'          => 'milex.report.campaign.log.date_triggered',
                'type'           => 'datetime',
                'alias'          => $aliasPrefix.'date_triggered',
                'groupByFormula' => 'DATE('.$prefix.'date_triggered)',
            ],
            $prefix.'is_scheduled' => [
                'label' => 'milex.report.campaign.log.is_scheduled',
                'type'  => 'boolean',
                'alias' => $aliasPrefix.'is_scheduled',
            ],
            $prefix.'trigger_date' => [
                'label'          => 'milex.report.campaign.log.trigger_date',
                'type'           => 'datetime',
                'alias'          => $aliasPrefix.'trigger_date',
                'groupByFormula' => 'DATE('.$prefix.'trigger_date)',
            ],
            $prefix.'system_triggered' => [
                'label' => 'milex.report.campaign.log.system_triggered',
                'type'  => 'boolean',
                'alias' => $aliasPrefix.'system_triggered',
            ],
            $prefix.'non_action_path_taken' => [
                'label' => 'milex.report.campaign.log.non_action_path_taken',
                'type'  => 'boolean',
                'alias' => $aliasPrefix.'non_action_path_taken',
            ],
            $prefix.'channel' => [
                'label' => 'milex.report.campaign.log.channel',
                'type'  => 'string',
                'alias' => $aliasPrefix.'channel',
            ],
            $prefix.'channel_id' => [
                'label' => 'milex.report.campaign.log.channel_id',
                'type'  => 'int',
                'alias' => $aliasPrefix.'channel_id',
            ],
            $prefix.'rotation' => [
                'label' => 'milex.report.campaign.event.rotation',
                'type'  => 'int',
                'alias' => $eventAliasPrefix.'rotation',
            ],

            // Event columns
            $eventPrefix.'name' => [
                'label' => 'milex.report.campaign.event.name',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'name',
            ],
            $eventPrefix.'description' => [
                'label' => 'milex.report.campaign.event.description',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'description',
            ],
            $eventPrefix.'type' => [
                'label' => 'milex.report.campaign.event.type',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'type',
            ],
            $eventPrefix.'event_type' => [
                'label' => 'milex.report.campaign.event.event_type',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'event_type',
            ],
            $eventPrefix.'trigger_date' => [
                'label'          => 'milex.report.campaign.event.trigger_date',
                'type'           => 'datetime',
                'alias'          => $eventAliasPrefix.'trigger_date',
                'groupByFormula' => 'DATE('.$eventPrefix.'trigger_date)',
            ],
            $eventPrefix.'trigger_mode' => [
                'label' => 'milex.report.campaign.event.trigger_mode',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'trigger_mode',
            ],
            $eventPrefix.'channel' => [
                'label' => 'milex.report.campaign.event.channel',
                'type'  => 'string',
                'alias' => $eventAliasPrefix.'channel',
            ],
            $eventPrefix.'channel_id' => [
                'label' => 'milex.report.campaign.event.channel_id',
                'type'  => 'int',
                'alias' => $eventAliasPrefix.'channel_id',
            ],
        ];

        $companyColumns = $this->companyReportData->getCompanyData();

        $columns = array_merge(
            $columns,
            $event->getStandardColumns($campaignPrefix, [], 'milex_campaign_action'),
            $event->getCategoryColumns($catPrefix),
            $event->getLeadColumns($leadPrefix),
            $event->getIpColumn(),
            $event->getChannelColumns(),
            $companyColumns
        );

        $data = [
            'display_name' => 'milex.campaign.events',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_CAMPAIGN_LEAD_EVENT_LOG, $data);

        // Register graphs
        //$event->addGraph($context, 'line', 'milex.page.graph.line.hits');
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if (!$event->checkContext(self::CONTEXT_CAMPAIGN_LEAD_EVENT_LOG)) {
            return;
        }

        $qb = $event->getQueryBuilder();

        $qb->from(MILEX_TABLE_PREFIX.'campaign_lead_event_log', 'log')
            ->leftJoin('log', MILEX_TABLE_PREFIX.'campaigns', 'c', 'c.id = log.campaign_id')
            ->leftJoin('log', MILEX_TABLE_PREFIX.'campaign_events', 'e', 'e.id = log.event_id');

        $event
            ->addLeadLeftJoin($qb, 'log')
            ->addIpAddressLeftJoin($qb, 'log')
            ->addCategoryLeftJoin($qb, 'c', 'cat')
            ->addChannelLeftJoins($qb, 'log');

        if ($this->companyReportData->eventHasCompanyColumns($event)) {
            $event->addCompanyLeftJoin($qb);
        }

        $event->applyDateFilters($qb, 'date_triggered', 'log');

        $event->setQueryBuilder($qb);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGraphGenerate(ReportGraphEvent $event)
    {
        if (!$event->checkContext(self::CONTEXT_CAMPAIGN_LEAD_EVENT_LOG)) {
            return;
        }

        $graphs = $event->getRequestedGraphs();
        $qb     = $event->getQueryBuilder();

        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;

            /** @var ChartQuery $chartQuery */
            $chartQuery = clone $options['chartQuery'];
            $chartQuery->applyDateFilters($queryBuilder, 'date_triggered', 'log');

            switch ($g) {
                /*
                case 'milex.page.graph.line.hits':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $chartQuery->modifyTimeDataQuery($queryBuilder, 'date_hit', 'ph');
                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;
                */
            }

            unset($queryBuilder);
        }
    }
}
