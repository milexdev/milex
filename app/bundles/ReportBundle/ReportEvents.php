<?php

namespace Milex\ReportBundle;

/**
 * Class ReportEvents.
 *
 * Events available for ReportBundle
 */
final class ReportEvents
{
    /**
     * The milex.report_pre_save event is dispatched right before a report is persisted.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    const REPORT_PRE_SAVE = 'milex.report_pre_save';

    /**
     * The milex.report_post_save event is dispatched right after a report is persisted.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    const REPORT_POST_SAVE = 'milex.report_post_save';

    /**
     * The milex.report_pre_delete event is dispatched prior to when a report is deleted.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    const REPORT_PRE_DELETE = 'milex.report_pre_delete';

    /**
     * The milex.report_post_delete event is dispatched after a report is deleted.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    const REPORT_POST_DELETE = 'milex.report_post_delete';

    /**
     * The milex.report_on_build event is dispatched before displaying the report builder form to allow
     * bundles to specify report sources and columns.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportBuilderEvent instance.
     *
     * @var string
     */
    const REPORT_ON_BUILD = 'milex.report_on_build';

    /**
     * The milex.report_on_generate event is dispatched when generating a report to build the base query.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportGeneratorEvent instance.
     *
     * @var string
     */
    const REPORT_ON_GENERATE = 'milex.report_on_generate';

    /**
     * The milex.report_query_pre_execute event is dispatched to allow a plugin to alter the query before execution.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportQueryEvent instance.
     *
     * @var string
     */
    const REPORT_QUERY_PRE_EXECUTE = 'milex.report_query_pre_execute';

    /**
     * The milex.report_on_display event is dispatched when displaying a report.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportDataEvent instance.
     *
     * @var string
     */
    const REPORT_ON_DISPLAY = 'milex.report_on_display';

    /**
     * The milex.report_on_graph_generate event is dispatched to generate a graph data.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportGraphEvent instance.
     *
     * @var string
     */
    const REPORT_ON_GRAPH_GENERATE = 'milex.report_on_graph_generate';

    /**
     * The milex.report_schedule_send event is dispatched to send an exported report to a user.
     *
     * The event listener receives a Milex\ReportBundle\Event\ReportScheduleSendEvent instance.
     *
     * @var string
     */
    const REPORT_SCHEDULE_SEND = 'milex.report_schedule_send';
}
