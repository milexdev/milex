<?php

namespace Milex\CalendarBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    /**
     * Generates the calendar data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function generateDataAction(Request $request)
    {
        $dates = [
            'start_date' => $request->query->get('start'),
            'end_date'   => $request->query->get('end'),
        ];

        /* @type \Milex\CalendarBundle\Model\CalendarModel $model */
        $model  = $this->getModel('calendar');
        $events = $model->getCalendarEvents($dates);

        $this->checkEventPermissions($events);

        // Can't use $this->sendJsonResponse, because it converts arrays to objects and Fullcalendar doesn't render events then.
        $response = new Response();
        $response->setContent(json_encode($events));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Updates an event on dragging the event around the calendar.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateEventAction(Request $request)
    {
        $entityId  = $request->request->get('entityId');
        $source    = $request->request->get('entityType');
        $setter    = 'set'.$request->request->get('setter');
        $dateValue = new \DateTime($request->request->get('startDate'));
        $response  = ['success' => false];

        /* @type \Milex\CalendarBundle\Model\CalendarModel $model */
        $calendarModel = $this->getModel('calendar');
        $event         = $calendarModel->editCalendarEvent($source, $entityId);

        $model  = $event->getModel();
        $entity = $event->getEntity();

        //not found
        if (null === $entity) {
            $this->addFlash('milex.core.error.notfound', 'error');
        } elseif (!$event->hasAccess()) {
            $this->addFlash('milex.core.error.accessdenied', 'error');
        } elseif ($model->isLocked($entity)) {
            $this->addFlash(
                'milex.core.error.locked',
                [
                    '%name%'      => $entity->getTitle(),
                    '%menu_link%' => 'milex_'.$source.'_index',
                    '%url%'       => $this->generateUrl(
                        'milex_'.$source.'_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ]
            );
        } elseif ('POST' == $this->request->getMethod()) {
            $entity->$setter($dateValue);
            $model->saveEntity($entity);
            $response['success'] = true;

            $this->addFlash(
                'milex.core.notice.updated',
                [
                    '%name%'      => $entity->getTitle(),
                    '%menu_link%' => 'milex_'.$source.'_index',
                    '%url%'       => $this->generateUrl(
                        'milex_'.$source.'_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ]
            );
        }

        //render flashes
        $response['flashes'] = $this->getFlashContent();

        return $this->sendJsonResponse($response);
    }

    /**
     * @param $events
     */
    public function checkEventPermissions(&$events)
    {
        $security     = $this->get('milex.security');
        $modelFactory = $this->get('milex.model.factory');

        foreach ($events as $key => $event) {
            //make sure the user has view access to the entities
            foreach ($event as $eventKey => $eventValue) {
                if ('_id' === substr($eventKey, -3)) {
                    $modelName = substr($eventKey, 0, -3);
                    if ($modelFactory->hasModel($modelName)) {
                        $model = $modelFactory->getModel($modelName);
                        $base  = $model->getPermissionBase();
                        if (!$security->isGranted([$base.':viewown', $base.':viewother'], 'MATCH_ONE')) {
                            unset($events[$key]);
                        }
                    }

                    break;
                }
            }
        }
    }
}
