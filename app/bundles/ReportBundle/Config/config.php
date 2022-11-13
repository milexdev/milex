<?php

return [
    'routes' => [
        'main' => [
            'milex_report_index' => [
                'path'       => '/reports/{page}',
                'controller' => 'MilexReportBundle:Report:index',
            ],
            'milex_report_export' => [
                'path'       => '/reports/view/{objectId}/export/{format}',
                'controller' => 'MilexReportBundle:Report:export',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'milex_report_download' => [
                'path'       => '/reports/download/{reportId}/{format}',
                'controller' => 'MilexReportBundle:Report:download',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'milex_report_view' => [
                'path'       => '/reports/view/{objectId}/{reportPage}',
                'controller' => 'MilexReportBundle:Report:view',
                'defaults'   => [
                    'reportPage' => 1,
                ],
                'requirements' => [
                    'reportPage' => '\d+',
                ],
            ],
            'milex_report_schedule_preview' => [
                'path'       => '/reports/schedule/preview/{isScheduled}/{scheduleUnit}/{scheduleDay}/{scheduleMonthFrequency}',
                'controller' => 'MilexReportBundle:Schedule:index',
                'defaults'   => [
                    'isScheduled'            => 0,
                    'scheduleUnit'           => '',
                    'scheduleDay'            => '',
                    'scheduleMonthFrequency' => '',
                ],
            ],
            'milex_report_schedule' => [
                'path'       => '/reports/schedule/{reportId}/now',
                'controller' => 'MilexReportBundle:Schedule:now',
            ],
            'milex_report_action' => [
                'path'       => '/reports/{objectAction}/{objectId}',
                'controller' => 'MilexReportBundle:Report:execute',
            ],
        ],
        'api' => [
            'milex_api_getreports' => [
                'path'       => '/reports',
                'controller' => 'MilexReportBundle:Api\ReportApi:getEntities',
            ],
            'milex_api_getreport' => [
                'path'       => '/reports/{id}',
                'controller' => 'MilexReportBundle:Api\ReportApi:getReport',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'milex.report.reports' => [
                'route'     => 'milex_report_index',
                'iconClass' => 'fa-line-chart',
                'access'    => [
                    'report:reports:viewown',
                    'report:reports:viewother',
                ],
                'priority' => 20,
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.report.configbundle.subscriber' => [
                'class' => \Milex\ReportBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.report.search.subscriber' => [
                'class'     => \Milex\ReportBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.helper.user',
                    'milex.report.model.report',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.report.report.subscriber' => [
                'class'     => \Milex\ReportBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.report.dashboard.subscriber' => [
                'class'     => \Milex\ReportBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.report.model.report',
                    'milex.security',
                ],
            ],
            'milex.report.scheduler.report_scheduler_subscriber' => [
                'class'     => \Milex\ReportBundle\Scheduler\EventListener\ReportSchedulerSubscriber::class,
                'arguments' => [
                    'milex.report.model.scheduler_planner',
                ],
            ],
            'milex.report.report.schedule_subscriber' => [
                'class'     => \Milex\ReportBundle\EventListener\SchedulerSubscriber::class,
                'arguments' => [
                    'milex.report.model.send_schedule',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.reportconfig' => [
                'class'     => \Milex\ReportBundle\Form\Type\ConfigType::class,
            ],
            'milex.form.type.report' => [
                'class'     => \Milex\ReportBundle\Form\Type\ReportType::class,
                'arguments' => [
                    'milex.report.model.report',
                ],
            ],
            'milex.form.type.filter_selector' => [
                'class' => \Milex\ReportBundle\Form\Type\FilterSelectorType::class,
            ],
            'milex.form.type.table_order' => [
                'class'     => \Milex\ReportBundle\Form\Type\TableOrderType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.report_filters' => [
                'class'     => 'Milex\ReportBundle\Form\Type\ReportFiltersType',
                'arguments' => 'milex.factory',
            ],
            'milex.form.type.report_dynamic_filters' => [
                'class' => 'Milex\ReportBundle\Form\Type\DynamicFiltersType',
            ],
            'milex.form.type.report_widget' => [
                'class'     => 'Milex\ReportBundle\Form\Type\ReportWidgetType',
                'arguments' => 'milex.report.model.report',
            ],
            'milex.form.type.aggregator' => [
                'class'     => \Milex\ReportBundle\Form\Type\AggregatorType::class,
                'arguments' => 'translator',
            ],
            'milex.form.type.report.settings' => [
                'class' => \Milex\ReportBundle\Form\Type\ReportSettingsType::class,
            ],
        ],
        'helpers' => [
            'milex.report.helper.report' => [
                'class' => \Milex\ReportBundle\Helper\ReportHelper::class,
                'alias' => 'report',
            ],
        ],
        'models' => [
            'milex.report.model.report' => [
                'class'     => \Milex\ReportBundle\Model\ReportModel::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.helper.templating',
                    'milex.channel.helper.channel_list',
                    'milex.lead.model.field',
                    'milex.report.helper.report',
                    'milex.report.model.csv_exporter',
                    'milex.report.model.excel_exporter',
                ],
            ],
            'milex.report.model.csv_exporter' => [
                'class'     => \Milex\ReportBundle\Model\CsvExporter::class,
                'arguments' => [
                    'milex.helper.template.formatter',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.report.model.excel_exporter' => [
                'class'     => \Milex\ReportBundle\Model\ExcelExporter::class,
                'arguments' => [
                    'milex.helper.template.formatter',
                ],
            ],
            'milex.report.model.scheduler_builder' => [
                'class'     => \Milex\ReportBundle\Scheduler\Builder\SchedulerBuilder::class,
                'arguments' => [
                    'milex.report.model.scheduler_template_factory',
                ],
            ],
            'milex.report.model.scheduler_template_factory' => [
                'class'     => \Milex\ReportBundle\Scheduler\Factory\SchedulerTemplateFactory::class,
                'arguments' => [],
            ],
            'milex.report.model.scheduler_date_builder' => [
                'class'     => \Milex\ReportBundle\Scheduler\Date\DateBuilder::class,
                'arguments' => [
                    'milex.report.model.scheduler_builder',
                ],
            ],
            'milex.report.model.scheduler_planner' => [
                'class'     => \Milex\ReportBundle\Scheduler\Model\SchedulerPlanner::class,
                'arguments' => [
                    'milex.report.model.scheduler_date_builder',
                    'doctrine.orm.default_entity_manager',
                ],
            ],
            'milex.report.model.send_schedule' => [
                'class'     => \Milex\ReportBundle\Scheduler\Model\SendSchedule::class,
                'arguments' => [
                    'milex.helper.mailer',
                    'milex.report.model.message_schedule',
                    'milex.report.model.file_handler',
                ],
            ],
            'milex.report.model.file_handler' => [
                'class'     => \Milex\ReportBundle\Scheduler\Model\FileHandler::class,
                'arguments' => [
                    'milex.helper.file_path_resolver',
                    'milex.helper.file_properties',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.report.model.message_schedule' => [
                'class'     => \Milex\ReportBundle\Scheduler\Model\MessageSchedule::class,
                'arguments' => [
                    'translator',
                    'milex.helper.file_properties',
                    'milex.helper.core_parameters',
                    'router',
                ],
            ],
            'milex.report.model.report_exporter' => [
                'class'     => \Milex\ReportBundle\Model\ReportExporter::class,
                'arguments' => [
                    'milex.report.model.schedule_model',
                    'milex.report.model.report_data_adapter',
                    'milex.report.model.report_export_options',
                    'milex.report.model.report_file_writer',
                    'event_dispatcher',
                ],
            ],
            'milex.report.model.schedule_model' => [
                'class'     => \Milex\ReportBundle\Model\ScheduleModel::class,
                'arguments' => [
                    'doctrine.orm.default_entity_manager',
                    'milex.report.model.scheduler_planner',
                ],
            ],
            'milex.report.model.report_data_adapter' => [
                'class'     => \Milex\ReportBundle\Adapter\ReportDataAdapter::class,
                'arguments' => [
                    'milex.report.model.report',
                ],
            ],
            'milex.report.model.report_export_options' => [
                'class'     => \Milex\ReportBundle\Model\ReportExportOptions::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.report.model.report_file_writer' => [
                'class'     => \Milex\ReportBundle\Model\ReportFileWriter::class,
                'arguments' => [
                    'milex.report.model.csv_exporter',
                    'milex.report.model.export_handler',
                ],
            ],
            'milex.report.model.export_handler' => [
                'class'     => \Milex\ReportBundle\Model\ExportHandler::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.helper.file_path_resolver',
                ],
            ],
        ],
        'validator' => [
            'milex.report.validator.schedule_is_valid_validator' => [
                'class'     => \Milex\ReportBundle\Scheduler\Validator\ScheduleIsValidValidator::class,
                'arguments' => [
                    'milex.report.model.scheduler_builder',
                ],
                'tag' => 'validator.constraint_validator',
            ],
        ],
        'command' => [
            'milex.report.command.export_scheduler' => [
                'class'     => \Milex\ReportBundle\Scheduler\Command\ExportSchedulerCommand::class,
                'arguments' => [
                    'milex.report.model.report_exporter',
                    'translator',
                ],
                'tag' => 'console.command',
            ],
        ],
        'fixtures' => [
            'milex.report.fixture.report' => [
                'class' => \Milex\ReportBundle\DataFixtures\ORM\LoadReportData::class,
                'tag'   => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
            ],
        ],
    ],

    'parameters' => [
        'report_temp_dir'                     => '%kernel.root_dir%/../media/files/temp',
        'report_export_batch_size'            => 1000,
        'report_export_max_filesize_in_bytes' => 5000000,
        'csv_always_enclose'                  => false,
    ],
];
