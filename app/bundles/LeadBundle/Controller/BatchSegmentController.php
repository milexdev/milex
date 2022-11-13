<?php

namespace Milex\LeadBundle\Controller;

use Milex\CoreBundle\Controller\AbstractFormController;
use Milex\LeadBundle\Form\Type\BatchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BatchSegmentController extends AbstractFormController
{
    private $actionModel;

    private $segmentModel;

    /**
     * Initialize object props here to simulate constructor
     * and make the future controller refactoring easier.
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->actionModel  = $this->container->get('milex.lead.model.segment.action');
        $this->segmentModel = $this->container->get('milex.lead.model.list');
    }

    /**
     * API for batch action.
     *
     * @return JsonResponse
     */
    public function setAction()
    {
        $params = $this->request->get('lead_batch', []);
        $ids    = empty($params['ids']) ? [] : json_decode($params['ids']);

        if ($ids && is_array($ids)) {
            $segmentsToAdd    = isset($params['add']) ? $params['add'] : [];
            $segmentsToRemove = isset($params['remove']) ? $params['remove'] : [];
            $contactIds       = json_decode($params['ids']);

            $this->actionModel->addContacts($contactIds, $segmentsToAdd);
            $this->actionModel->removeContacts($contactIds, $segmentsToRemove);

            $this->addFlash('milex.lead.batch_leads_affected', [
                '%count%'     => count($ids),
            ]);
        } else {
            $this->addFlash('milex.core.error.ids.missing');
        }

        return new JsonResponse([
            'closeModal' => true,
            'flashes'    => $this->getFlashContent(),
        ]);
    }

    /**
     * View for batch action.
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $route = $this->generateUrl('milex_segment_batch_contact_set');
        $lists = $this->segmentModel->getUserLists();
        $items = [];

        foreach ($lists as $list) {
            $items[$list['name']] = $list['id'];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $this->createForm(
                        BatchType::class,
                        [],
                        [
                            'items'  => $items,
                            'action' => $route,
                        ]
                    )->createView(),
                ],
                'contentTemplate' => 'MilexLeadBundle:Batch:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'leadBatch',
                    'route'         => $route,
                ],
            ]
        );
    }
}
