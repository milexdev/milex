<?php

namespace Milex\PageBundle\EventListener;

use Milex\CoreBundle\Helper\Chart\ChartQuery;
use Milex\CoreBundle\Helper\Chart\LineChart;
use Milex\CoreBundle\Helper\Chart\PieChart;
use Milex\LeadBundle\Model\CompanyReportData;
use Milex\PageBundle\Entity\HitRepository;
use Milex\ReportBundle\Event\ReportBuilderEvent;
use Milex\ReportBundle\Event\ReportGeneratorEvent;
use Milex\ReportBundle\Event\ReportGraphEvent;
use Milex\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_PAGES      = 'pages';
    const CONTEXT_PAGE_HITS  = 'page.hits';
    const CONTEXT_VIDEO_HITS = 'video.hits';

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    /**
     * @var HitRepository
     */
    private $hitRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        CompanyReportData $companyReportData,
        HitRepository $hitRepository,
        TranslatorInterface $translator
    ) {
        $this->companyReportData = $companyReportData;
        $this->hitRepository     = $hitRepository;
        $this->translator        = $translator;
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
        if (!$event->checkContext([self::CONTEXT_PAGES, self::CONTEXT_PAGE_HITS, self::CONTEXT_VIDEO_HITS])) {
            return;
        }

        $prefix            = 'p.';
        $translationPrefix = 'tp.';
        $variantPrefix     = 'vp.';

        $columns = [
            $prefix.'title' => [
                'label' => 'milex.core.title',
                'type'  => 'string',
            ],
            $prefix.'alias' => [
                'label' => 'milex.core.alias',
                'type'  => 'string',
            ],
            $prefix.'revision' => [
                'label' => 'milex.page.report.revision',
                'type'  => 'string',
            ],
            $prefix.'hits' => [
                'label' => 'milex.page.field.hits',
                'type'  => 'int',
            ],
            $prefix.'unique_hits' => [
                'label' => 'milex.page.field.unique_hits',
                'type'  => 'int',
            ],
            $translationPrefix.'id' => [
                'label' => 'milex.page.report.translation_parent_id',
                'type'  => 'int',
            ],
            $translationPrefix.'title' => [
                'label' => 'milex.page.report.translation_parent_title',
                'type'  => 'string',
            ],
            $variantPrefix.'id' => [
                'label' => 'milex.page.report.variant_parent_id',
                'type'  => 'string',
            ],
            $variantPrefix.'title' => [
                'label' => 'milex.page.report.variant_parent_title',
                'type'  => 'string',
            ],
            $prefix.'lang' => [
                'label' => 'milex.core.language',
                'type'  => 'string',
            ],
            $prefix.'variant_start_date' => [
                'label'          => 'milex.page.report.variant_start_date',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE('.$prefix.'variant_start_date)',
            ],
            $prefix.'variant_hits' => [
                'label' => 'milex.page.report.variant_hits',
                'type'  => 'int',
            ],
        ];
        $columns = array_merge(
            $columns,
            $event->getStandardColumns('p.', ['name', 'description'], 'milex_page_action'),
            $event->getCategoryColumns()
        );
        $data = [
            'display_name' => 'milex.page.pages',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_PAGES, $data);

        if ($event->checkContext(self::CONTEXT_PAGE_HITS)) {
            $hitPrefix   = 'ph.';
            $redirectHit = 'r.';
            $hitColumns  = [
                $hitPrefix.'id' => [
                    'label' => 'milex.page.report.hits.id',
                    'type'  => 'int',
                ],
                $hitPrefix.'date_hit' => [
                    'label'          => 'milex.page.report.hits.date_hit',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$hitPrefix.'date_hit)',
                ],
                $hitPrefix.'date_left' => [
                    'label'          => 'milex.page.report.hits.date_left',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$hitPrefix.'date_left)',
                ],
                $hitPrefix.'country' => [
                    'label' => 'milex.page.report.hits.country',
                    'type'  => 'string',
                ],
                $hitPrefix.'region' => [
                    'label' => 'milex.page.report.hits.region',
                    'type'  => 'string',
                ],
                $hitPrefix.'city' => [
                    'label' => 'milex.page.report.hits.city',
                    'type'  => 'string',
                ],
                $hitPrefix.'isp' => [
                    'label' => 'milex.page.report.hits.isp',
                    'type'  => 'string',
                ],
                $hitPrefix.'organization' => [
                    'label' => 'milex.page.report.hits.organization',
                    'type'  => 'string',
                ],
                $hitPrefix.'code' => [
                    'label' => 'milex.page.report.hits.code',
                    'type'  => 'int',
                ],
                $hitPrefix.'referer' => [
                    'label' => 'milex.page.report.hits.referer',
                    'type'  => 'string',
                ],
                $hitPrefix.'url' => [
                    'label' => 'milex.page.report.hits.url',
                    'type'  => 'url',
                ],
                $hitPrefix.'url_title' => [
                    'label' => 'milex.page.report.hits.url_title',
                    'type'  => 'string',
                ],
                $hitPrefix.'user_agent' => [
                    'label' => 'milex.page.report.hits.user_agent',
                    'type'  => 'string',
                ],
                $hitPrefix.'remote_host' => [
                    'label' => 'milex.page.report.hits.remote_host',
                    'type'  => 'string',
                ],
                $hitPrefix.'browser_languages' => [
                    'label' => 'milex.page.report.hits.browser_languages',
                    'type'  => 'array',
                ],
                $hitPrefix.'source' => [
                    'label' => 'milex.report.field.source',
                    'type'  => 'string',
                ],
                $hitPrefix.'source_id' => [
                    'label' => 'milex.report.field.source_id',
                    'type'  => 'int',
                ],
                $redirectHit.'url' => [
                    'label' => 'milex.page.report.hits.redirect_url',
                    'type'  => 'url',
                ],
                $redirectHit.'hits' => [
                    'label' => 'milex.page.report.hits.redirect_hit_count',
                    'type'  => 'int',
                ],
                $redirectHit.'unique_hits' => [
                    'label' => 'milex.page.report.hits.redirect_unique_hits',
                    'type'  => 'string',
                ],
                'ds.device' => [
                    'label' => 'milex.lead.device',
                    'type'  => 'string',
                ],
                'ds.device_brand' => [
                    'label' => 'milex.lead.device_brand',
                    'type'  => 'string',
                ],
                'ds.device_model' => [
                    'label' => 'milex.lead.device_model',
                    'type'  => 'string',
                ],
                'ds.device_os_name' => [
                    'label' => 'milex.lead.device_os_name',
                    'type'  => 'string',
                ],
                'ds.device_os_shortname' => [
                    'label' => 'milex.lead.device_os_shortname',
                    'type'  => 'string',
                ],
                'ds.device_os_version' => [
                    'label' => 'milex.lead.device_os_version',
                    'type'  => 'string',
                ],
                'ds.device_os_platform' => [
                    'label' => 'milex.lead.device_os_platform',
                    'type'  => 'string',
                ],
            ];

            $companyColumns = $this->companyReportData->getCompanyData();

            $pageHitsColumns = array_merge(
                $columns,
                $hitColumns,
                $event->getCampaignByChannelColumns(),
                $event->getLeadColumns(),
                $event->getIpColumn(),
                $companyColumns
            );

            $data = [
                'display_name' => 'milex.page.hits',
                'columns'      => $pageHitsColumns,
            ];
            $event->addTable(self::CONTEXT_PAGE_HITS, $data, self::CONTEXT_PAGES);

            // Register graphs
            $context = self::CONTEXT_PAGE_HITS;
            $event->addGraph($context, 'line', 'milex.page.graph.line.hits');
            $event->addGraph($context, 'line', 'milex.page.graph.line.time.on.site');
            $event->addGraph($context, 'pie', 'milex.page.graph.pie.time.on.site', ['translate' => false]);
            $event->addGraph($context, 'pie', 'milex.page.graph.pie.new.vs.returning');
            $event->addGraph($context, 'pie', 'milex.page.graph.pie.devices');
            $event->addGraph($context, 'pie', 'milex.page.graph.pie.languages', ['translate' => false]);
            $event->addGraph($context, 'table', 'milex.page.table.referrers');
            $event->addGraph($context, 'table', 'milex.page.table.most.visited');
            $event->addGraph($context, 'table', 'milex.page.table.most.visited.unique');
        }
        if ($event->checkContext(self::CONTEXT_VIDEO_HITS)) {
            $hitPrefix  = 'vh.';
            $hitColumns = [
                $hitPrefix.'id' => [
                    'label' => 'milex.core.id',
                    'type'  => 'int',
                ],
                $hitPrefix.'date_hit' => [
                    'label'          => 'milex.page.report.hits.date_hit',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$hitPrefix.'date_hit)',
                ],
                $hitPrefix.'country' => [
                    'label' => 'milex.page.report.hits.country',
                    'type'  => 'string',
                ],
                $hitPrefix.'region' => [
                    'label' => 'milex.page.report.hits.region',
                    'type'  => 'string',
                ],
                $hitPrefix.'city' => [
                    'label' => 'milex.page.report.hits.city',
                    'type'  => 'string',
                ],
                $hitPrefix.'isp' => [
                    'label' => 'milex.page.report.hits.isp',
                    'type'  => 'string',
                ],
                $hitPrefix.'organization' => [
                    'label' => 'milex.page.report.hits.organization',
                    'type'  => 'string',
                ],
                $hitPrefix.'code' => [
                    'label' => 'milex.page.report.hits.code',
                    'type'  => 'int',
                ],
                $hitPrefix.'referer' => [
                    'label' => 'milex.page.report.hits.referer',
                    'type'  => 'string',
                ],
                $hitPrefix.'url' => [
                    'label' => 'milex.page.report.hits.url',
                    'type'  => 'url',
                ],
                $hitPrefix.'user_agent' => [
                    'label' => 'milex.page.report.hits.user_agent',
                    'type'  => 'string',
                ],
                $hitPrefix.'remote_host' => [
                    'label' => 'milex.page.report.hits.remote_host',
                    'type'  => 'string',
                ],
                $hitPrefix.'browser_languages' => [
                    'label' => 'milex.page.report.hits.browser_languages',
                    'type'  => 'array',
                ],
                $hitPrefix.'channel' => [
                    'label' => 'milex.report.field.source',
                    'type'  => 'string',
                ],
                $hitPrefix.'channel_id' => [
                    'label' => 'milex.report.field.source_id',
                    'type'  => 'int',
                ],
                'time_watched' => [
                    'label'   => 'milex.page.report.hits.time_watched',
                    'type'    => 'string',
                    'formula' => 'if('.$hitPrefix.'duration = 0,\'-\',SEC_TO_TIME('.$hitPrefix.'time_watched))',
                ],
                'duration' => [
                    'label'   => 'milex.page.report.hits.duration',
                    'type'    => 'string',
                    'formula' => 'if('.$hitPrefix.'duration = 0,\'-\',SEC_TO_TIME('.$hitPrefix.'duration))',
                ],
            ];

            $data = [
                'display_name' => 'milex.'.self::CONTEXT_VIDEO_HITS,
                'columns'      => array_merge($hitColumns, $event->getLeadColumns(), $event->getIpColumn()),
            ];
            $event->addTable(self::CONTEXT_VIDEO_HITS, $data, 'videos');
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $context    = $event->getContext();
        $qb         = $event->getQueryBuilder();
        $hasGroupBy = $event->hasGroupBy();

        switch ($context) {
            case self::CONTEXT_PAGES:
                $qb->from(MILEX_TABLE_PREFIX.'pages', 'p')
                    ->leftJoin('p', MILEX_TABLE_PREFIX.'pages', 'tp', 'p.id = tp.id')
                    ->leftJoin('p', MILEX_TABLE_PREFIX.'pages', 'vp', 'p.id = vp.id');
                $event->addCategoryLeftJoin($qb, 'p');
                break;
            case self::CONTEXT_PAGE_HITS:
                $event->applyDateFilters($qb, 'date_hit', 'ph');

                $qb->from(MILEX_TABLE_PREFIX.'page_hits', 'ph')
                    ->leftJoin('ph', MILEX_TABLE_PREFIX.'pages', 'p', 'ph.page_id = p.id')
                    ->leftJoin('p', MILEX_TABLE_PREFIX.'pages', 'tp', 'p.id = tp.id')
                    ->leftJoin('p', MILEX_TABLE_PREFIX.'pages', 'vp', 'p.id = vp.id')
                    ->leftJoin('ph', MILEX_TABLE_PREFIX.'page_redirects', 'r', 'r.id = ph.redirect_id')
                    ->leftJoin('ph', MILEX_TABLE_PREFIX.'lead_devices', 'ds', 'ds.id = ph.device_id');

                $event->addIpAddressLeftJoin($qb, 'ph');
                $event->addCategoryLeftJoin($qb, 'p');
                $event->addLeadLeftJoin($qb, 'ph');
                $event->addCampaignByChannelJoin($qb, 'p', 'page');

                if ($this->companyReportData->eventHasCompanyColumns($event)) {
                    $event->addCompanyLeftJoin($qb);
                }

                break;
            case 'video.hits':
                if (!$hasGroupBy) {
                    $qb->groupBy('vh.id');
                }
                $event->applyDateFilters($qb, 'date_hit', 'vh');

                $qb->from(MILEX_TABLE_PREFIX.'video_hits', 'vh');

                $event->addIpAddressLeftJoin($qb, 'vh');
                $event->addLeadLeftJoin($qb, 'vh');
                break;
        }
        $event->setQueryBuilder($qb);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGraphGenerate(ReportGraphEvent $event)
    {
        // Context check, we only want to fire for Lead reports
        if (!$event->checkContext(self::CONTEXT_PAGE_HITS)) {
            return;
        }

        $graphs = $event->getRequestedGraphs();
        $qb     = $event->getQueryBuilder();

        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;

            /** @var ChartQuery $chartQuery */
            $chartQuery = clone $options['chartQuery'];
            $chartQuery->applyDateFilters($queryBuilder, 'date_hit', 'ph');

            switch ($g) {
                case 'milex.page.graph.line.hits':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $chartQuery->modifyTimeDataQuery($queryBuilder, 'date_hit', 'ph');
                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'milex.page.graph.line.time.on.site':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $queryBuilder->select('TIMESTAMPDIFF(SECOND, ph.date_hit, ph.date_left) as data, ph.date_hit as date');
                    $queryBuilder->andWhere($qb->expr()->isNotNull('ph.date_left'));

                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'milex.page.graph.pie.time.on.site':
                    $timesOnSite = $this->hitRepository->getDwellTimeLabels();
                    $chart       = new PieChart();

                    foreach ($timesOnSite as $time) {
                        $q = clone $queryBuilder;
                        $chartQuery->modifyCountDateDiffQuery($q, 'date_hit', 'date_left', $time['from'], $time['till'], 'ph');
                        $data = $chartQuery->fetchCountDateDiff($q);
                        $chart->setDataset($time['label'], $data);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-clock-o',
                        ]
                    );
                    break;

                case 'milex.page.graph.pie.new.vs.returning':
                    $chart   = new PieChart();
                    $allQ    = clone $queryBuilder;
                    $uniqueQ = clone $queryBuilder;
                    $chartQuery->modifyCountQuery($allQ, 'date_hit', [], 'ph');
                    $chartQuery->modifyCountQuery($uniqueQ, 'date_hit', ['getUnique' => true, 'selectAlso' => ['ph.page_id']], 'ph');
                    $all       = $chartQuery->fetchCount($allQ);
                    $unique    = $chartQuery->fetchCount($uniqueQ);
                    $returning = $all - $unique;
                    $chart->setDataset($this->translator->trans('milex.page.unique'), $unique);
                    $chart->setDataset($this->translator->trans('milex.page.graph.pie.new.vs.returning.returning'), $returning);

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-bookmark-o',
                        ]
                    );
                    break;

                case 'milex.page.graph.pie.languages':
                    $queryBuilder->select('ph.page_language, COUNT(distinct(ph.id)) as the_count')
                        ->groupBy('ph.page_language')
                        ->andWhere($qb->expr()->isNotNull('ph.page_language'));
                    $data  = $queryBuilder->execute()->fetchAll();
                    $chart = new PieChart();

                    foreach ($data as $lang) {
                        $chart->setDataset($lang['page_language'], $lang['the_count']);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-globe',
                        ]
                    );
                    break;
                case 'milex.page.graph.pie.devices':
                    $queryBuilder->select('ds.device, COUNT(distinct(ph.id)) as the_count')
                        ->groupBy('ds.device');
                    $data  = $queryBuilder->execute()->fetchAll();
                    $chart = new PieChart();

                    foreach ($data as $device) {
                        $label = substr(empty($device['device']) ? $this->translator->trans('milex.core.no.info') : $device['device'], 0, 12);
                        $chart->setDataset($label, $device['the_count']);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-globe',
                        ]
                    );
                    break;
                case 'milex.page.table.referrers':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->hitRepository->getReferers($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-sign-in';
                    $event->setGraph($g, $graphData);
                    break;

                case 'milex.page.table.most.visited':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->hitRepository->getMostVisited($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-eye';
                    $graphData['link']      = 'milex_page_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'milex.page.table.most.visited.unique':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->hitRepository->getMostVisited($queryBuilder, $limit, $offset, 'p.unique_hits', 'sessions');
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-eye';
                    $graphData['link']      = 'milex_page_action';
                    $event->setGraph($g, $graphData);
                    break;
            }

            unset($queryBuilder);
        }
    }
}
