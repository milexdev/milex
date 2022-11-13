<?php

namespace Milex\LeadBundle\Segment\Stat;

use Milex\CampaignBundle\Model\CampaignModel;
use Milex\EmailBundle\Model\EmailModel;
use Milex\FormBundle\Model\ActionModel;
use Milex\LeadBundle\Model\ListModel;
use Milex\PointBundle\Model\TriggerEventModel;
use Milex\ReportBundle\Model\ReportModel;

class SegmentDependencies
{
    /**
     * @var EmailModel
     */
    private $emailModel;

    /**
     * @var CampaignModel
     */
    private $campaignModel;

    /**
     * @var ActionModel
     */
    private $actionModel;

    /**
     * @var ListModel
     */
    private $listModel;

    /**
     * @var TriggerEventModel
     */
    private $triggerEventModel;

    /**
     * @var ReportModel
     */
    private $reportModel;

    public function __construct(EmailModel $emailModel, CampaignModel $campaignModel, ActionModel $actionModel, ListModel $listModel, TriggerEventModel $triggerEventModel, ReportModel $reportModel)
    {
        $this->emailModel        = $emailModel;
        $this->campaignModel     = $campaignModel;
        $this->actionModel       = $actionModel;
        $this->listModel         = $listModel;
        $this->triggerEventModel = $triggerEventModel;
        $this->reportModel       = $reportModel;
    }

    /**
     * @param $segmentId
     *
     * @return array
     */
    public function getChannelsIds($segmentId)
    {
        $usage   = [];
        $usage[] = [
            'label' => 'milex.email.emails',
            'route' => 'milex_email_index',
            'ids'   => $this->emailModel->getEmailsIdsWithDependenciesOnSegment($segmentId),
        ];

        $usage[] = [
            'label' => 'milex.campaign.campaigns',
            'route' => 'milex_campaign_index',
            'ids'   => $this->campaignModel->getCampaignIdsWithDependenciesOnSegment($segmentId),
        ];

        $usage[] = [
            'label' => 'milex.lead.lead.lists',
            'route' => 'milex_segment_index',
            'ids'   => $this->listModel->getSegmentsWithDependenciesOnSegment($segmentId, 'id'),
        ];

        $usage[] = [
            'label' => 'milex.report.reports',
            'route' => 'milex_report_index',
            'ids'   => $this->reportModel->getReportsIdsWithDependenciesOnSegment($segmentId),
        ];

        $usage[] = [
            'label' => 'milex.form.forms',
            'route' => 'milex_form_index',
            'ids'   => $this->actionModel->getFormsIdsWithDependenciesOnSegment($segmentId),
        ];

        $usage[] = [
            'label' => 'milex.point.trigger.header.index',
            'route' => 'milex_pointtrigger_index',
            'ids'   => $this->triggerEventModel->getReportIdsWithDependenciesOnSegment($segmentId),
        ];

        return $usage;
    }
}
