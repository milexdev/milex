<?php

namespace Milex\FormBundle\EventListener;

use Milex\CoreBundle\Helper\Chart\LineChart;
use Milex\FormBundle\Entity\SubmissionRepository;
use Milex\LeadBundle\Model\CompanyReportData;
use Milex\ReportBundle\Event\ReportBuilderEvent;
use Milex\ReportBundle\Event\ReportGeneratorEvent;
use Milex\ReportBundle\Event\ReportGraphEvent;
use Milex\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_FORMS           = 'forms';
    const CONTEXT_FORM_SUBMISSION = 'form.submissions';

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    /**
     * @var SubmissionRepository
     */
    private $submissionRepository;

    public function __construct(CompanyReportData $companyReportData, SubmissionRepository $submissionRepository)
    {
        $this->companyReportData    = $companyReportData;
        $this->submissionRepository = $submissionRepository;
    }

    /**
     * @return array
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
        if (!$event->checkContext([self::CONTEXT_FORMS, self::CONTEXT_FORM_SUBMISSION])) {
            return;
        }

        // Forms
        $prefix  = 'f.';
        $columns = [
            $prefix.'alias' => [
                'label' => 'milex.core.alias',
                'type'  => 'string',
            ],
        ];
        $columns = array_merge(
            $columns,
            $event->getStandardColumns($prefix, [], 'milex_form_action'),
            $event->getCategoryColumns()
        );
        $data = [
            'display_name' => 'milex.form.forms',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_FORMS, $data);

        if ($event->checkContext(self::CONTEXT_FORM_SUBMISSION)) {
            // Form submissions
            $submissionPrefix  = 'fs.';
            $pagePrefix        = 'p.';
            $submissionColumns = [
                $submissionPrefix.'date_submitted' => [
                    'label'          => 'milex.form.report.submit.date_submitted',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$submissionPrefix.'date_submitted)',
                ],
                $submissionPrefix.'referer' => [
                    'label' => 'milex.core.referer',
                    'type'  => 'string',
                ],
                $pagePrefix.'id' => [
                    'label' => 'milex.form.report.page_id',
                    'type'  => 'int',
                    'link'  => 'milex_page_action',
                ],
                $pagePrefix.'title' => [
                    'label' => 'milex.form.report.page_name',
                    'type'  => 'string',
                ],
            ];

            $companyColumns = $this->companyReportData->getCompanyData();

            $formSubmissionColumns = array_merge(
                $submissionColumns,
                $columns,
                $event->getCampaignByChannelColumns(),
                $event->getLeadColumns(),
                $event->getIpColumn(),
                $companyColumns
            );

            $data = [
                'display_name' => 'milex.form.report.submission.table',
                'columns'      => $formSubmissionColumns,
            ];
            $event->addTable(self::CONTEXT_FORM_SUBMISSION, $data, self::CONTEXT_FORMS);

            // Register graphs
            $context = self::CONTEXT_FORM_SUBMISSION;
            $event->addGraph($context, 'line', 'milex.form.graph.line.submissions');
            $event->addGraph($context, 'table', 'milex.form.table.top.referrers');
            $event->addGraph($context, 'table', 'milex.form.table.most.submitted');
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if (!$event->checkContext([self::CONTEXT_FORMS, self::CONTEXT_FORM_SUBMISSION])) {
            return;
        }

        $context = $event->getContext();
        $qb      = $event->getQueryBuilder();

        switch ($context) {
            case self::CONTEXT_FORMS:
                $qb->from(MILEX_TABLE_PREFIX.'forms', 'f');
                $event->addCategoryLeftJoin($qb, 'f');
                break;
            case self::CONTEXT_FORM_SUBMISSION:
                $event->applyDateFilters($qb, 'date_submitted', 'fs');

                $qb->from(MILEX_TABLE_PREFIX.'form_submissions', 'fs')
                    ->leftJoin('fs', MILEX_TABLE_PREFIX.'forms', 'f', 'f.id = fs.form_id')
                    ->leftJoin('fs', MILEX_TABLE_PREFIX.'pages', 'p', 'p.id = fs.page_id');
                $event->addCategoryLeftJoin($qb, 'f');
                $event->addLeadLeftJoin($qb, 'fs');
                $event->addIpAddressLeftJoin($qb, 'fs');
                $event->addCampaignByChannelJoin($qb, 'f', 'form');

                if ($this->companyReportData->eventHasCompanyColumns($event)) {
                    $event->addCompanyLeftJoin($qb);
                }

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
        if (!$event->checkContext(self::CONTEXT_FORM_SUBMISSION)) {
            return;
        }

        $graphs = $event->getRequestedGraphs();
        $qb     = $event->getQueryBuilder();

        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;
            $chartQuery   = clone $options['chartQuery'];
            $chartQuery->applyDateFilters($queryBuilder, 'date_submitted', 'fs');

            switch ($g) {
                case 'milex.form.graph.line.submissions':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $chartQuery->modifyTimeDataQuery($queryBuilder, 'date_submitted', 'fs');
                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'milex.form.table.top.referrers':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->submissionRepository->getTopReferrers($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-sign-in';
                    $graphData['link']      = 'milex_form_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'milex.form.table.most.submitted':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->submissionRepository->getMostSubmitted($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-check-square-o';
                    $graphData['link']      = 'milex_form_action';
                    $event->setGraph($g, $graphData);
                    break;
            }
            unset($queryBuilder);
        }
    }
}
