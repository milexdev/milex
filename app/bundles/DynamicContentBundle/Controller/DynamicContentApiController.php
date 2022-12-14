<?php

namespace Milex\DynamicContentBundle\Controller;

use Milex\CoreBundle\Controller\CommonController;
use Milex\DynamicContentBundle\Helper\DynamicContentHelper;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingServiceInterface;
use Milex\PageBundle\Model\PageModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DynamicContentApiController.
 */
class DynamicContentApiController extends CommonController
{
    /**
     * @param $objectAlias
     *
     * @return mixed
     */
    public function processAction($objectAlias)
    {
        // Don't store a visitor with this request
        defined('MILEX_NON_TRACKABLE_REQUEST') || define('MILEX_NON_TRACKABLE_REQUEST', 1);

        $method = $this->request->getMethod();
        if (method_exists($this, $method.'Action')) {
            return $this->{$method.'Action'}($objectAlias);
        } else {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'This endpoint is not able to process '.strtoupper($method).' requests.');
        }
    }

    public function getAction($objectAlias)
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead');
        /** @var DynamicContentHelper $helper */
        $helper = $this->get('milex.helper.dynamicContent');
        /** @var DeviceTrackingServiceInterface $deviceTrackingService */
        $deviceTrackingService = $this->get('milex.lead.service.device_tracking_service');
        /** @var PageModel $pageModel */
        $pageModel = $this->getModel('page');

        /** @var Lead $lead */
        $lead          = $model->getContactFromRequest($pageModel->getHitQuery($this->request));
        $content       = $helper->getDynamicContentForLead($objectAlias, $lead);
        $trackedDevice = $deviceTrackingService->getTrackedDevice();
        $deviceId      = (null === $trackedDevice ? null : $trackedDevice->getTrackingId());

        return empty($content)
            ? new Response('', Response::HTTP_NO_CONTENT)
            : new JsonResponse(
                [
                    'content'   => $content,
                    'id'        => $lead->getId(),
                    'sid'       => $deviceId,
                    'device_id' => $deviceId,
                ]
            );
    }
}
