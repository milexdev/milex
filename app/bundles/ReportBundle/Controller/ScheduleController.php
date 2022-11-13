<?php

namespace Milex\ReportBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Milex\CoreBundle\Service\FlashBag;
use Milex\ReportBundle\Scheduler\Date\DateBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScheduleController extends CommonAjaxController
{
    public function indexAction($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency)
    {
        /** @var DateBuilder $dateBuilder */
        $dateBuilder = $this->container->get('milex.report.model.scheduler_date_builder');
        $dates       = $dateBuilder->getPreviewDays($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency);

        $html = $this->render(
            'MilexReportBundle:Schedule:index.html.php',
            [
                'dates' => $dates,
            ]
        )->getContent();

        return $this->sendJsonResponse(
            [
                'html' => $html,
            ]
        );
    }

    /**
     * Sets report to schedule NOW if possible.
     *
     * @param int $reportId
     *
     * @return JsonResponse
     */
    public function nowAction($reportId)
    {
        /** @var \Milex\ReportBundle\Model\ReportModel $model */
        $model = $this->getModel('report');

        /** @var \Milex\ReportBundle\Entity\Report $report */
        $report = $model->getEntity($reportId);

        /** @var \Milex\CoreBundle\Security\Permissions\CorePermissions $security */
        $security = $this->container->get('milex.security');

        if (empty($report)) {
            $this->addFlash('milex.report.notfound', ['%id%' => $reportId], FlashBag::LEVEL_ERROR, 'messages');

            return $this->flushFlash();
        }

        if (!$security->hasEntityAccess('report:reports:viewown', 'report:reports:viewother', $report->getCreatedBy())) {
            $this->addFlash('milex.core.error.accessdenied', [], FlashBag::LEVEL_ERROR);

            return $this->flushFlash();
        }

        if ($report->isScheduled()) {
            $this->addFlash('milex.report.scheduled.already', ['%id%' => $reportId], FlashBag::LEVEL_ERROR);

            return $this->flushFlash();
        }

        $report->setAsScheduledNow($this->user->getEmail());
        $model->saveEntity($report);

        $this->addFlash(
            'milex.report.scheduled.to.now',
            ['%id%' => $reportId, '%email%' => $this->user->getEmail()]
        );

        return $this->flushFlash();
    }

    /**
     * @return JsonResponse
     */
    private function flushFlash()
    {
        return new JsonResponse(['flashes' => $this->getFlashContent()]);
    }
}
