<?php

namespace Milex\NotificationBundle\Controller\Api;

use Milex\ApiBundle\Controller\CommonApiController;
use Milex\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class NotificationApiController.
 */
class NotificationApiController extends CommonApiController
{
    /**
     * @var ContactTracker
     */
    protected $contactTracker;

    /**
     * {@inheritdoc}
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->contactTracker  = $this->container->get('milex.tracker.contact');
        $this->model           = $this->getModel('notification');
        $this->entityClass     = 'Milex\NotificationBundle\Entity\Notification';
        $this->entityNameOne   = 'notification';
        $this->entityNameMulti = 'notifications';

        parent::initialize($event);
    }

    /**
     * Receive Web Push subscription request.
     *
     * @return JsonResponse
     */
    public function subscribeAction()
    {
        $osid = $this->request->get('osid');
        if ($osid) {
            /** @var \Milex\LeadBundle\Model\LeadModel $leadModel */
            $leadModel = $this->getModel('lead');

            if ($currentLead = $this->contactTracker->getContact()) {
                $currentLead->addPushIDEntry($osid);
                $leadModel->saveEntity($currentLead);
            }

            return new JsonResponse(['success' => true, 'osid' => $osid], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        return new JsonResponse(['success' => 'false'], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
